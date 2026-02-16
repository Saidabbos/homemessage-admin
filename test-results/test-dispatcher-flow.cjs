const { chromium } = require('playwright');

const BASE = 'http://localhost:8081';

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();

    page.on('console', msg => {
        if (msg.type() === 'error') console.log(`   [JS ERROR] ${msg.text()}`);
    });

    // Monitor network POST requests
    page.on('request', req => {
        if (req.method() === 'POST') {
            console.log(`   [POST] ${req.url()}`);
        }
    });
    page.on('response', resp => {
        if (resp.request().method() === 'POST' && !resp.url().includes('/admin/login')) {
            console.log(`   [RESP] ${resp.status()} ${resp.url()}`);
        }
    });

    console.log('=== DISPATCHER FLOW TEST ===\n');

    // ==================
    // HELPER: click visible button by text (handles both UZ and RU)
    // ==================
    async function clickButton(texts, options = {}) {
        const textsArr = Array.isArray(texts) ? texts : [texts];
        for (const text of textsArr) {
            const loc = options.parent
                ? page.locator(`${options.parent} >> button:has-text("${text}")`)
                : page.locator(`button:has-text("${text}")`);
            if (await loc.count() > 0) {
                const target = options.last ? loc.last() : loc.first();
                if (await target.isVisible().catch(() => false)) {
                    await target.click();
                    return text;
                }
            }
            // Also try <a> tags
            const aLoc = options.parent
                ? page.locator(`${options.parent} >> a:has-text("${text}")`)
                : page.locator(`a:has-text("${text}")`);
            if (await aLoc.count() > 0) {
                const target = options.last ? aLoc.last() : aLoc.first();
                if (await target.isVisible().catch(() => false)) {
                    await target.click();
                    return text;
                }
            }
        }
        return null;
    }

    // ==================
    // Step 1: Login
    // ==================
    console.log('1. Logging in as dispatcher...');
    await page.goto(`${BASE}/admin/login`);
    await page.waitForLoadState('networkidle');

    await page.locator('#email').click();
    await page.keyboard.type('dispatcher@example.com');
    await page.locator('#password').click();
    await page.keyboard.type('password');

    await page.locator('button[type="submit"]').click();

    try {
        await page.waitForURL(url => !url.toString().includes('/admin/login'), { timeout: 8000 });
    } catch (e) {
        console.log('   URL did not change within 8s, checking page state...');
    }

    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);

    let afterLoginUrl = page.url();
    console.log(`   After login URL: ${afterLoginUrl}`);

    if (afterLoginUrl.includes('/admin/login')) {
        console.log('   Inertia form did not redirect. Trying direct navigation...');

        const result = await page.evaluate(async (base) => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const xsrfMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            const xsrfToken = xsrfMatch ? decodeURIComponent(xsrfMatch[1]) : '';

            const resp = await fetch(`${base}/admin/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-XSRF-TOKEN': xsrfToken,
                    'Accept': 'text/html',
                },
                body: new URLSearchParams({
                    email: 'dispatcher@example.com',
                    password: 'password',
                    remember: '',
                }).toString(),
                credentials: 'same-origin',
                redirect: 'manual',
            });
            return { status: resp.status, location: resp.headers.get('location'), type: resp.type };
        }, BASE);
        console.log(`   Fetch result: ${JSON.stringify(result)}`);

        await page.goto(`${BASE}/admin/dashboard`);
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);
        afterLoginUrl = page.url();
        console.log(`   Dashboard URL: ${afterLoginUrl}`);
    }

    if (!afterLoginUrl.includes('/admin/dashboard')) {
        console.log('   LOGIN FAILED - aborting');
        await page.screenshot({ path: 'test-results/disp-failed.png' });
        await browser.close();
        return;
    }

    console.log('   LOGIN SUCCESSFUL!');
    await page.screenshot({ path: 'test-results/disp-01-dashboard.png' });

    // ==================
    // Step 2: Try /booking (should redirect)
    // ==================
    console.log('\n2. Trying to access /booking...');
    await page.goto(`${BASE}/booking`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    const bookingUrl = page.url();
    console.log(`   Booking redirect URL: ${bookingUrl}`);
    await page.screenshot({ path: 'test-results/disp-02-booking-redirect.png' });

    if (bookingUrl.includes('/admin/dashboard')) {
        console.log('   PASS: Redirected to admin dashboard');
    } else if (bookingUrl.includes('/booking')) {
        console.log('   FAIL: Dispatcher can still access booking page!');
    } else {
        console.log(`   Redirected to: ${bookingUrl}`);
    }

    // ==================
    // Step 3: Landing page - CTAs hidden
    // ==================
    console.log('\n3. Visiting landing page...');
    await page.goto(`${BASE}/`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'test-results/disp-03-landing.png' });

    const navCta = await page.$('.nav-cta');
    const heroCta = await page.$('.hero-cta-primary');
    const navUser = await page.$('.nav-user-link');
    console.log(`   Nav booking CTA visible: ${navCta !== null} ${navCta === null ? '(PASS)' : '(FAIL)'}`);
    console.log(`   Hero booking CTA visible: ${heroCta !== null} ${heroCta === null ? '(PASS)' : '(FAIL)'}`);
    if (navUser) {
        const href = await navUser.getAttribute('href');
        console.log(`   User link href: ${href} ${href === '/admin/dashboard' ? '(PASS)' : '(FAIL)'}`);
    }

    const authData = await page.evaluate(() => {
        const el = document.getElementById('app');
        if (el?.__vue_app__) return el.__vue_app__.config.globalProperties.$page?.props?.auth;
        return null;
    });
    console.log(`   Auth: role=${authData?.user?.role}, name=${authData?.user?.name}`);

    // ==================
    // Step 4: /masters - CTA hidden
    // ==================
    console.log('\n4. Visiting /masters page...');
    await page.goto(`${BASE}/masters`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'test-results/disp-04-masters.png' });

    const mpNavCta = await page.$('.mp-nav-cta');
    console.log(`   Masters nav booking CTA visible: ${mpNavCta !== null} ${mpNavCta === null ? '(PASS)' : '(FAIL)'}`);

    // ==================
    // Step 5: Master detail - CTAs hidden
    // ==================
    const masterLink = await page.$('.mp-master-card');
    if (masterLink) {
        const masterHref = await masterLink.getAttribute('href');
        console.log(`\n5. Visiting master detail: ${masterHref}...`);
        await page.goto(`${BASE}${masterHref}`);
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);
        await page.screenshot({ path: 'test-results/disp-05-master-detail.png' });

        const detailNavCta = await page.$('.md-nav-cta');
        const detailHeroCta = await page.$('.md-cta-primary');
        const detailBottomCta = await page.$('.md-cta-btn-primary');
        console.log(`   Detail nav CTA visible: ${detailNavCta !== null} ${detailNavCta === null ? '(PASS)' : '(FAIL)'}`);
        console.log(`   Detail hero CTA visible: ${detailHeroCta !== null} ${detailHeroCta === null ? '(PASS)' : '(FAIL)'}`);
        console.log(`   Detail bottom CTA visible: ${detailBottomCta !== null} ${detailBottomCta === null ? '(PASS)' : '(FAIL)'}`);
    }

    // ============================
    // ORDERS MANAGEMENT TESTS
    // ============================

    // ==================
    // Step 6: Orders list
    // ==================
    console.log('\n6. Navigating to orders list...');
    await page.goto(`${BASE}/admin/orders`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'test-results/disp-06-orders-list.png' });

    const orderLinks = await page.$$('a[href*="/admin/orders/"]');
    const showLinks = [];
    for (const link of orderLinks) {
        const href = await link.getAttribute('href');
        if (href && href.match(/\/admin\/orders\/\d+/)) {
            showLinks.push(link);
        }
    }

    console.log(`   Found ${showLinks.length} order links`);

    if (showLinks.length === 0) {
        console.log('   No orders found. Skipping order management tests.');
        console.log('\n=== BOOKING RESTRICTION TESTS PASSED (no orders to test) ===');
        await browser.close();
        return;
    }

    // ==================
    // Step 7: Open order detail (prefer NEW status order for full flow test)
    // ==================
    console.log('\n7. Opening order detail...');
    // Try to find a non-completed order for testing status changes
    let selectedLink = showLinks[0];
    for (let i = 1; i < Math.min(showLinks.length, 10); i++) {
        // Click through first few to find one that's not COMPLETED/CANCELLED
        const href = await showLinks[i].getAttribute('href');
        if (href) {
            selectedLink = showLinks[i];
            break;  // Take the second order (first might be COMPLETED from previous runs)
        }
    }
    const firstOrderLink = selectedLink;
    const orderHref = await firstOrderLink.getAttribute('href');
    const orderUrl = orderHref.startsWith('http') ? orderHref : `${BASE}${orderHref}`;
    console.log(`   Navigating to: ${orderUrl}`);
    await page.goto(orderUrl);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'test-results/disp-07-order-detail.png' });

    // Read order props
    const orderProps = await page.evaluate(() => {
        const el = document.getElementById('app');
        if (el?.__vue_app__) {
            const order = el.__vue_app__.config.globalProperties.$page?.props?.order;
            // Also get the confirmation-related keys
            const confKeys = Object.keys(order || {}).filter(k => k.startsWith('call_') || k.startsWith('conf_'));
            return {
                id: order?.id,
                status: order?.status,
                call_outcome: order?.call_outcome,
                order_number: order?.order_number,
                confKeys: confKeys,
            };
        }
        return null;
    });
    console.log(`   Order: #${orderProps?.order_number}, status=${orderProps?.status}, call_outcome=${orderProps?.call_outcome}`);
    console.log(`   Confirmation keys: ${JSON.stringify(orderProps?.confKeys)}`);

    // ==================
    // Step 8: Fill confirmation form (Anketa)
    // ==================
    console.log('\n8. Filling confirmation form (Anketa)...');

    // Open confirmation modal - try both UZ and RU button texts
    // "To'ldirish" (UZ) / "Заполнить" (RU) or "Tahrirlash" / "Редактировать" or "Anketa to'ldirish"
    const anketaTexts = ["To'ldirish", "Tahrirlash", "Anketa", "Заполнить", "Редактировать", "Анкета"];
    const clickedAnketa = await clickButton(anketaTexts);
    console.log(`   Clicked anketa button: ${clickedAnketa || 'NOT FOUND'}`);

    if (clickedAnketa) {
        await page.waitForTimeout(500);

        // Verify modal opened
        const modalHeader = page.locator('text=Анкета подтверждения');
        const modalVisible = await modalHeader.count() > 0;
        console.log(`   Confirmation modal visible: ${modalVisible}`);
        await page.screenshot({ path: 'test-results/disp-08-anketa-modal.png' });

        if (modalVisible) {
            // Select call_outcome = "Tasdiqlandi" (confirmed)
            console.log('   Selecting call outcome...');
            const outcomeClicked = await clickButton(['Tasdiqlandi', 'Подтверждено']);
            console.log(`   Call outcome: ${outcomeClicked || 'NOT FOUND'}`);

            // Fill text fields via keyboard
            console.log('   Filling form fields...');

            // Fill entrance
            const entranceInput = page.locator('input[placeholder="1"]').first();
            if (await entranceInput.isVisible().catch(() => false)) {
                await entranceInput.click();
                await page.keyboard.press('Control+a');
                await page.keyboard.type('2');
            }

            // Fill floor
            const floorInput = page.locator('input[placeholder="3"]').first();
            if (await floorInput.isVisible().catch(() => false)) {
                await floorInput.click();
                await page.keyboard.press('Control+a');
                await page.keyboard.type('5');
            }

            // Check elevator
            const elevatorLabel = page.locator('label:has-text("Lift bor"), label:has-text("Лифт")').first();
            if (await elevatorLabel.isVisible().catch(() => false)) {
                await elevatorLabel.click();
            }

            // Fill parking
            const parkingInput = page.locator('input[placeholder*="Hovlida"], input[placeholder*="Парковка"]').first();
            if (await parkingInput.isVisible().catch(() => false)) {
                await parkingInput.click();
                await page.keyboard.press('Control+a');
                await page.keyboard.type('Hovlida, 3-joy');
            }

            // Fill landmark
            const landmarkInput = page.locator('input[placeholder*="Makro"], input[placeholder*="Ориентир"]').first();
            if (await landmarkInput.isVisible().catch(() => false)) {
                await landmarkInput.click();
                await page.keyboard.press('Control+a');
                await page.keyboard.type('Katta bozor yonida');
            }

            // Fill onsite phone
            const phoneInput = page.locator('input[placeholder*="+998"]').first();
            if (await phoneInput.isVisible().catch(() => false)) {
                await phoneInput.click();
                await page.keyboard.press('Control+a');
                await page.keyboard.type('+998901234567');
            }

            // Fill constraints
            const constraintsTextarea = page.locator('textarea[placeholder*="Oyoqqa"], textarea[placeholder*="Аллергия"]').first();
            if (await constraintsTextarea.isVisible().catch(() => false)) {
                await constraintsTextarea.click();
                await page.keyboard.press('Control+a');
                await page.keyboard.type('Oyoqqa massaj qilmang');
            }

            // Check space_ok
            const spaceLabel = page.locator('label:has-text("2×2"), label:has-text("2x2")').first();
            if (await spaceLabel.isVisible().catch(() => false)) {
                await spaceLabel.click();
            }

            // Fill note to master
            const masterNoteTextarea = page.locator('textarea[placeholder*="shimcha"], textarea[placeholder*="Дополнительно"]').first();
            if (await masterNoteTextarea.isVisible().catch(() => false)) {
                await masterNoteTextarea.click();
                await page.keyboard.press('Control+a');
                await page.keyboard.type('Test izoh - 5-qavat, lift bor');
            }

            await page.waitForTimeout(300);
            await page.screenshot({ path: 'test-results/disp-09-anketa-filled.png' });

            // Submit: click Save button (hard-coded "Saqlash" in confirmation modal)
            console.log('   Submitting confirmation form...');
            // Set up response waiter BEFORE clicking
            const confirmRespPromise = page.waitForResponse(
                resp => resp.url().includes('/confirmation') && resp.request().method() === 'POST',
                { timeout: 10000 }
            ).catch(() => null);
            const saveClicked = await clickButton(['Saqlash', 'Сохранить']);
            console.log(`   Clicked save: ${saveClicked || 'NOT FOUND'}`);

            if (saveClicked) {
                // Wait for POST response
                const postResp = await confirmRespPromise;
                console.log(`   POST response: ${postResp?.status()}`);
                // Wait for Inertia to follow redirect and reload page data
                await page.waitForResponse(
                    resp => resp.url().includes('/admin/orders/') && resp.request().method() === 'GET',
                    { timeout: 10000 }
                ).catch(() => null);
                await page.waitForTimeout(1000);
                await page.waitForLoadState('networkidle');
            }

            await page.screenshot({ path: 'test-results/disp-10-anketa-saved.png' });

            // Verify - try reading props first
            let savedOutcome = await page.evaluate(() => {
                const el = document.getElementById('app');
                if (el?.__vue_app__) {
                    return el.__vue_app__.config.globalProperties.$page?.props?.order?.call_outcome;
                }
                return null;
            });
            console.log(`   call_outcome (before reload): ${savedOutcome}`);

            // If not found, reload page to get fresh data
            if (!savedOutcome || savedOutcome === 'pending') {
                console.log('   Reloading page for fresh data...');
                await page.reload({ waitUntil: 'networkidle' });
                await page.waitForTimeout(1000);

                savedOutcome = await page.evaluate(() => {
                    const el = document.getElementById('app');
                    if (el?.__vue_app__) {
                        const order = el.__vue_app__.config.globalProperties.$page?.props?.order;
                        return { call_outcome: order?.call_outcome, conf_entrance: order?.conf_entrance };
                    }
                    return null;
                });
                console.log(`   After reload: ${JSON.stringify(savedOutcome)}`);
            }

            const outcome = typeof savedOutcome === 'object' ? savedOutcome?.call_outcome : savedOutcome;
            console.log(`   Anketa result: ${outcome === 'confirmed' ? 'PASS' : 'CHECK - ' + outcome}`);
        }
    } else {
        console.log('   Could not open anketa modal. Skipping.');
    }

    // ==================
    // Step 9: Change order status
    // ==================
    console.log('\n9. Changing order status...');

    // Re-read current status
    const currentStatus = await page.evaluate(() => {
        const el = document.getElementById('app');
        if (el?.__vue_app__) {
            return el.__vue_app__.config.globalProperties.$page?.props?.order?.status;
        }
        return null;
    });
    console.log(`   Current status: ${currentStatus}`);

    // Status labels (server-side returns Uzbek labels always)
    const statusTransitions = {
        'NEW': { target: 'CONFIRMING', label: 'Tasdiqlanmoqda' },
        'CONFIRMING': { target: 'CONFIRMED', label: 'Tasdiqlangan' },
        'CONFIRMED': { target: 'IN_PROGRESS', label: 'Jarayonda' },
        'IN_PROGRESS': { target: 'COMPLETED', label: 'Yakunlangan' },
    };

    const transition = statusTransitions[currentStatus];
    if (transition) {
        console.log(`   Transitioning: ${currentStatus} → ${transition.target} (${transition.label})`);

        // Click the status transition button
        const statusClicked = await clickButton([transition.label]);
        console.log(`   Status button clicked: ${statusClicked || 'NOT FOUND'}`);

        if (statusClicked) {
            await page.waitForTimeout(500);
            await page.screenshot({ path: 'test-results/disp-11-status-modal.png' });

            // Fill comment in modal textarea
            const modalTextarea = page.locator('.fixed textarea').first();
            if (await modalTextarea.isVisible().catch(() => false)) {
                await modalTextarea.click();
                await page.keyboard.type('Test status change by dispatcher');
                console.log('   Comment filled');
            }

            // Click Save (uses i18n: "Saqlash" UZ / "Сохранить" RU)
            const statusRespPromise = page.waitForResponse(
                resp => resp.url().includes('/status') && resp.request().method() === 'POST',
                { timeout: 10000 }
            ).catch(() => null);
            const statusSaveClicked = await clickButton(['Saqlash', 'Сохранить']);
            console.log(`   Clicked save: ${statusSaveClicked || 'NOT FOUND'}`);

            if (statusSaveClicked) {
                const statusPostResp = await statusRespPromise;
                console.log(`   POST response: ${statusPostResp?.status()}`);
                // Wait for Inertia redirect
                await page.waitForResponse(
                    resp => resp.url().includes('/admin/orders/') && resp.request().method() === 'GET',
                    { timeout: 10000 }
                ).catch(() => null);
                await page.waitForTimeout(1000);
                await page.waitForLoadState('networkidle');
            }

            // Verify
            const newStatus = await page.evaluate(() => {
                const el = document.getElementById('app');
                if (el?.__vue_app__) {
                    return el.__vue_app__.config.globalProperties.$page?.props?.order?.status;
                }
                return null;
            });
            console.log(`   New status: ${newStatus} ${newStatus === transition.target ? '(PASS)' : '(FAIL - expected ' + transition.target + ')'}`);
            await page.screenshot({ path: 'test-results/disp-12-status-changed.png' });
        }
    } else {
        console.log(`   Status "${currentStatus}" has no forward transition. Skipping.`);
    }

    // Close any open modal (press Escape)
    await page.keyboard.press('Escape');
    await page.waitForTimeout(300);

    // ==================
    // Step 10: Add dispatcher note
    // ==================
    console.log('\n10. Adding dispatcher note...');

    // "Izoh qo'shish" (UZ) / "Добавить заметку" (RU)
    const noteClicked = await clickButton(["Izoh qo'shish", "Добавить заметку", "Izoh", "Добавить"]);
    console.log(`   Note button clicked: ${noteClicked || 'NOT FOUND'}`);

    if (noteClicked) {
        await page.waitForTimeout(500);

        // Fill note textarea in modal
        const noteTextarea = page.locator('.fixed textarea').first();
        if (await noteTextarea.isVisible().catch(() => false)) {
            await noteTextarea.click();
            await page.keyboard.type('Dispatcher test note - Mijoz bilan boglandim, soat 10:00 da kelishildi.');
            console.log('   Note text filled');

            await page.screenshot({ path: 'test-results/disp-13-note-modal.png' });

            // Click Save
            const noteRespPromise = page.waitForResponse(
                resp => resp.url().includes('/note') && resp.request().method() === 'POST',
                { timeout: 10000 }
            ).catch(() => null);
            const noteSaveClicked = await clickButton(['Saqlash', 'Сохранить']);
            console.log(`   Clicked save: ${noteSaveClicked || 'NOT FOUND'}`);

            if (noteSaveClicked) {
                const notePostResp = await noteRespPromise;
                console.log(`   POST response: ${notePostResp?.status()}`);
                await page.waitForResponse(
                    resp => resp.url().includes('/admin/orders/') && resp.request().method() === 'GET',
                    { timeout: 10000 }
                ).catch(() => null);
                await page.waitForTimeout(1000);
                await page.waitForLoadState('networkidle');
            }

            // Verify
            const noteVisible = await page.locator('text=Dispatcher test note').count();
            console.log(`   Note visible on page: ${noteVisible > 0 ? 'PASS' : 'CHECK SCREENSHOT'}`);
            await page.screenshot({ path: 'test-results/disp-14-note-saved.png' });
        } else {
            console.log('   Note modal textarea not visible');
        }
    } else {
        // Debug: print visible buttons in the notes area
        console.log('   Note button not found. Dumping visible buttons...');
        const allButtons = await page.evaluate(() => {
            return Array.from(document.querySelectorAll('button, a'))
                .filter(el => el.offsetWidth > 0 || el.closest('.fixed'))
                .map(b => ({ tag: b.tagName, text: b.textContent.trim().substring(0, 60), vis: b.offsetWidth > 0 }))
                .filter(b => b.text.length > 0)
                .slice(0, 40);
        });
        console.log(`   Buttons: ${JSON.stringify(allButtons.map(b => b.text))}`);
    }

    // ==================
    // Step 11: Final verification
    // ==================
    console.log('\n11. Final verification - orders list...');
    await page.goto(`${BASE}/admin/orders`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'test-results/disp-15-orders-final.png' });

    const finalOrderCount = await page.$$eval('a[href*="/admin/orders/"]', links =>
        links.filter(l => l.href.match(/\/admin\/orders\/\d+/)).length
    );
    console.log(`   Orders visible: ${finalOrderCount}`);

    console.log('\n=== ALL TESTS COMPLETED ===');
    await browser.close();
})();
