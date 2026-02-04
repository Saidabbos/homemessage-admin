# Setup and Testing Instructions for Inertia Vue Admin Panel

## 🚀 Quick Start

### Prerequisites
- Docker running with containers (app, nginx, mysql)
- Node.js 20.15+ (note: Vite recommends 20.19+, but our setup works)
- Git

### Step 1: Build Frontend Assets

```bash
# Build assets for production
npm run build

# Or run dev server with hot reload
npm run dev
```

### Step 2: Run Laravel Server

```bash
# Using Docker
docker compose exec -T app php artisan serve --host=0.0.0.0

# Or directly if PHP is installed
php artisan serve
```

### Step 3: Access the Admin Panel

**URL:** http://localhost:8000/admin/login

**Test Credentials:**
```
Email: admin@example.com
Password: password
```

---

## 🔍 Verification Checklist

### Pre-Flight Checks
- [ ] `npm run build` completes without errors
- [ ] No JavaScript console errors (F12 > Console)
- [ ] Page loads without CSRF token errors
- [ ] Images are served correctly from public/build

### Authentication Flow
- [ ] Login page loads and displays correctly
- [ ] Invalid credentials show error message
- [ ] Valid login redirects to dashboard
- [ ] "Remember me" checkbox works
- [ ] Logout button works and redirects to login
- [ ] Accessing /admin when not logged in redirects to login

### Dashboard Page
- [ ] Dashboard loads with stats cards
- [ ] All 6 stat numbers display correctly
- [ ] Purple gradient background displays
- [ ] Sidebar is visible and styled correctly
- [ ] User info in header shows correct name/avatar
- [ ] Dropdown menu appears when clicking ⋮

### Service Types - List Page
- [ ] Page loads with list of massage types
- [ ] Images display as thumbnails
- [ ] Price and duration format correctly
- [ ] Status badge shows (green "Faol" or red "Nofaol")
- [ ] Pagination links appear if more than 10 items
- [ ] "View", "Edit", "Delete" buttons are clickable

### Service Types - Create Page
- [ ] Page loads with empty form
- [ ] Slug auto-generates when typing in Uzbek name
- [ ] Image upload preview shows when selecting file
- [ ] All three language sections are visible
- [ ] Form submits successfully
- [ ] Success message appears
- [ ] Page redirects to list view

### Service Types - Edit Page
- [ ] Form pre-populates with existing data
- [ ] All three language translations display
- [ ] Current image displays
- [ ] Can upload new image to replace old one
- [ ] Form updates successfully
- [ ] Validation errors display inline

### Service Types - Show Page
- [ ] Large image displays
- [ ] All stats visible (price, duration, slug, status)
- [ ] All three language translations display correctly
- [ ] Metadata (created/updated dates) shows
- [ ] Edit button navigates to edit page
- [ ] Delete button works with confirmation

### Form & Input Validation
- [ ] Required field shows error when empty
- [ ] Price field only accepts numbers
- [ ] Duration shows min/max validation error
- [ ] Email field validates email format
- [ ] Error messages appear in red below inputs
- [ ] Errors clear when you fix the issue

### Visual & UX
- [ ] All text is readable with good contrast
- [ ] All buttons are clickable and show hover effect
- [ ] Form inputs are properly aligned
- [ ] Tables have proper zebra striping on hover
- [ ] Flash messages appear at top and auto-hide
- [ ] No console errors when interacting with page

### Browser Compatibility
- [ ] Tested in Chrome/Chromium
- [ ] Tested in Firefox
- [ ] Works on tablet size (responsive)
- [ ] Back/forward buttons work correctly

---

## 🛠️ Troubleshooting

### Issue: "Module not found" error

**Solution:**
```bash
# Clear cache and rebuild
rm -rf node_modules
npm install
npm run build
```

### Issue: CSRF token errors

**Solution:**
1. Check that `@csrf` is in `resources/views/app.blade.php`
2. Verify session is working: Check browser cookies for PHPSESSID
3. Clear browser cache and reload

### Issue: Images not displaying

**Solution:**
```bash
# Create storage symlink
php artisan storage:link

# Verify in public folder
ls -la public/storage
```

### Issue: Inertia page not rendering

**Solution:**
1. Check that `HandleInertiaRequests` middleware is registered
2. Verify in `bootstrap/app.php`:
   ```php
   $middleware->web(append: [
       \App\Http\Middleware\HandleInertiaRequests::class,
   ]);
   ```
3. Clear config cache: `php artisan config:clear`

### Issue: Vue components not loading

**Solution:**
1. Ensure components are in correct directories
2. Check component names match routes (e.g., `Admin/ServiceTypes/Index.vue`)
3. Verify vite.config.js has correct glob patterns

---

## 📊 Testing Scenarios

### Scenario 1: Create a New Massage Type
1. Login as admin
2. Navigate to "💆 Massage Turlari" in sidebar
3. Click "➕ Yangi Turl Qo'shish"
4. Fill in form:
   - Slug: `test-massage`
   - Duration: `90`
   - Price: `750000`
   - Upload an image
   - English name: `Test Massage`
   - Uzbek name: `Test Massaji`
   - Russian name: `Тестовый массаж`
5. Click "💾 Saqlash"
6. Verify success message appears
7. Verify new item appears in list

### Scenario 2: Edit Existing Massage Type
1. From list view, click "Tahrir" on any item
2. Change price to 600000
3. Upload new image
4. Click "💾 Yangilash"
5. Verify success message
6. Go back to list and verify changes

### Scenario 3: View Details
1. From list view, click "Ko'rish"
2. Verify all translations display
3. Click "✏️ Tahrir Qilish"
4. Verify edit form loads with data
5. Click "← Orqaga" to return

### Scenario 4: Delete Item
1. From show/edit page, click "🗑️ O'chirish"
2. Confirm deletion in popup
3. Verify success message
4. Verify item no longer in list

---

## 🔧 Advanced Verification

### Check Vite Build Output

```bash
# Rebuild and check file sizes
npm run build

# Expected output should show:
# ✓ 779 modules transformed
# ✓ built in ~90s
```

### Verify Assets Load

Open browser DevTools (F12):
1. Network tab
2. Filter by CSS/JS
3. Verify `public/build/assets/app-*.css` loads
4. Verify `public/build/assets/app-*.js` loads
5. Check no 404 errors

### Check Database Queries

```bash
# Enable query logging
docker compose exec -T app php artisan tinker

# Test pagination
>>> DB::enableQueryLog(); App\Models\ServiceType::paginate(10); DB::getQueryLog();
```

---

## 📈 Performance Benchmarks

Expected metrics (from build output):
- **CSS**: ~60 KB (uncompressed), ~12 KB (gzip)
- **JS**: ~262 KB (uncompressed), ~93 KB (gzip)
- **Build time**: ~90-120 seconds
- **Page load**: <1 second (after first load)

---

## 🚨 Common Mistakes to Avoid

❌ **Don't:** Run both `npm run dev` and `npm run build`
- Choose one or the other

❌ **Don't:** Forget to run `php artisan storage:link`
- Images won't display without this

❌ **Don't:** Edit Blade files after migration
- All changes should be in Vue now

❌ **Don't:** Clear browser cache during testing
- It's harder to spot real issues

✅ **Do:** Commit changes to Git
```bash
git add .
git commit -m "Implement Inertia Vue migration"
```

---

## 📞 Support

If issues persist:

1. **Check Logs:**
   ```bash
   # Laravel logs
   tail -f storage/logs/laravel.log

   # Browser console
   F12 > Console tab
   ```

2. **Clear Caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

3. **Rebuild Everything:**
   ```bash
   rm -rf node_modules public/build
   npm install
   npm run build
   ```

4. **Database Check:**
   ```bash
   php artisan migrate:refresh --seed
   ```

---

## ✨ Success Indicators

You'll know the migration is successful when:

✅ Login page loads at http://localhost:8000/admin/login
✅ Can login with admin@example.com / password
✅ Dashboard displays with 6 stat cards
✅ "💆 Massage Turlari" shows list of service types
✅ Can create new massage type with image
✅ Can edit existing massage type
✅ Can view details of any massage type
✅ Can delete massage type with confirmation
✅ Flash messages appear and auto-hide
✅ No red errors in browser console
✅ All styling looks clean and professional

---

## 🎉 Next Steps After Verification

Once everything is verified:

1. Deploy to staging environment
2. Run full QA testing
3. Document any custom changes
4. Remove old Blade template files (backup first)
5. Update deployment documentation
6. Train team on Vue component structure
7. Consider implementing additional features (composables, TypeScript, etc.)

---

**Last Updated:** February 5, 2026
**Status:** Ready for Testing ✅
