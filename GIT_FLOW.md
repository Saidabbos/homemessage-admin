# 🔀 Git Flow - Sabai

## Branch Strategy

```
master (production)
  │
  └── develop (staging)
        │
        ├── feature/xxx (yangi feature)
        ├── sprint-N (sprint branch)
        ├── bugfix/xxx (bug fix)
        └── hotfix/xxx (production hotfix)
```

---

## 📋 Branchlar

### `master`
- **Maqsad:** Production-ready kod
- **Deploy:** Auto-deploy to production
- **Rule:** Faqat `develop` yoki `hotfix/*` dan merge
- **Protection:** Direct push TAQIQLANGAN

### `develop`
- **Maqsad:** Staging/test uchun
- **Deploy:** Auto-deploy to staging
- **Rule:** Feature branchlar shu yerga merge bo'ladi
- **Test:** QA testing shu yerda

### `feature/*`
- **Format:** `feature/task-name` yoki `feature/GT-XXX-description`
- **Misol:** `feature/GT-020-slots-calendar`
- **Rule:** `develop` dan branch, `develop` ga merge

### `sprint-N`
- **Format:** `sprint-1`, `sprint-2`, etc.
- **Maqsad:** Sprint davomidagi barcha ishlar
- **Rule:** Sprint tugagach `develop` ga merge

### `bugfix/*`
- **Format:** `bugfix/issue-description`
- **Rule:** `develop` dan branch, `develop` ga merge

### `hotfix/*`
- **Format:** `hotfix/critical-issue`
- **Rule:** `master` dan branch, `master` VA `develop` ga merge
- **Foydalanish:** Faqat CRITICAL production buglar uchun

---

## 🔄 Workflow

### Yangi Feature

```bash
# 1. develop dan yangi branch
git checkout develop
git pull origin develop
git checkout -b feature/GT-020-slots-calendar

# 2. Ishlash
# ... kod yozish ...
git add .
git commit -m "feat(slots): add calendar component"

# 3. Push
git push -u origin feature/GT-020-slots-calendar

# 4. PR yaratish (GitHub)
# develop ← feature/GT-020-slots-calendar

# 5. Review & Merge
# Squash merge recommended
```

### Sprint Workflow

```bash
# Sprint boshi
git checkout develop
git pull origin develop
git checkout -b sprint-2

# Sprint davomida
git add .
git commit -m "feat(booking): add booking form"
git push origin sprint-2

# Sprint oxiri
git checkout develop
git pull origin develop
git merge sprint-2
git push origin develop

# Production ga chiqarish
git checkout master
git pull origin master
git merge develop
git push origin master
git tag -a v1.2.0 -m "Sprint 2 release"
git push origin --tags
```

### Hotfix

```bash
# 1. master dan hotfix branch
git checkout master
git pull origin master
git checkout -b hotfix/fix-payment-bug

# 2. Fix qilish
git add .
git commit -m "fix(payment): resolve duplicate charge issue"

# 3. master ga merge
git checkout master
git merge hotfix/fix-payment-bug
git push origin master

# 4. develop ga ham merge
git checkout develop
git merge hotfix/fix-payment-bug
git push origin develop

# 5. Tag
git checkout master
git tag -a v1.1.1 -m "Hotfix: payment bug"
git push origin --tags
```

---

## 📝 Commit Message Format

```
<type>(<scope>): <description>

[optional body]
[optional footer]
```

### Types:
- `feat` — Yangi feature
- `fix` — Bug fix
- `docs` — Documentation
- `style` — Formatting (kod logic o'zgarmaydi)
- `refactor` — Refactoring
- `test` — Test qo'shish
- `chore` — Build, config, etc.

### Misollar:
```
feat(booking): add booking form with validation
fix(slots): resolve 6-hour rule calculation
docs(readme): update installation instructions
refactor(api): extract slot service
test(booking): add unit tests for order creation
chore(docker): update PHP version to 8.2
```

---

## 🏷️ Versioning (SemVer)

```
v{MAJOR}.{MINOR}.{PATCH}

MAJOR — Breaking changes
MINOR — Yangi features (backward compatible)
PATCH — Bug fixes
```

**Misollar:**
- `v1.0.0` — Initial release
- `v1.1.0` — Sprint 2 features
- `v1.1.1` — Bug fix
- `v2.0.0` — Major rewrite

---

## ⚠️ Qoidalar

1. **HECH QACHON** `master` ga direct push qilmang
2. **HECH QACHON** `develop` ga direct push qilmang (kichik fixlar bundan mustasno)
3. Feature branch nomida **ticket ID** bo'lsin
4. PR da **test** o'tgan bo'lishi kerak
5. PR da kamida **1 review** bo'lishi kerak
6. Merge conflict o'zingiz hal qiling, keyin PR

---

## 🤖 AI Agent Workflow

AI agent ishlaganda:

```bash
# 1. Sprint branch yaratish
git checkout -b sprint-2

# 2. Task bo'yicha commit
git commit -m "feat(GT-020): slots calendar component"
git commit -m "feat(GT-021): slots API endpoint"

# 3. Har bir logical unit — alohida commit
# Test o'tgandan keyin push

# 4. Ready bo'lganda PR yoki direct merge (develop ga)
```

---

## 📁 Current Branches

```
master      — Production (hm.make-it.uz)
develop     — Staging (kerak bo'lsa yaratamiz)
sprint-2    — Hozirgi sprint (yaratiladi)
```

---

*Oxirgi yangilanish: 2025-02-05*
