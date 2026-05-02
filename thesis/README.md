# راهنمای کامپایل پایان‌نامه

## فایل اصلی

فایل اصلی پایان‌نامه در این پوشه:

```text
AUTthesis.tex
```

این فایل بر پایه‌ی قالب رسمی دانشگاه تنظیم شده است و فصل‌های فعال آن از
فایل‌های `chapter1.tex` تا `chapter6.tex` بارگذاری می‌شوند.

> فایل `thesis.tex` مربوط به نسخه‌ی اولیه‌ی غیررسمی است و دیگر ورودی اصلی
> پایان‌نامه محسوب نمی‌شود.

## پیش‌نیازها

### ۱. نصب توزیع LaTeX

برای ویندوز می‌توانید از **MiKTeX** یا **TeX Live** استفاده کنید.

### ۲. فونت‌های مورد نیاز قالب AUT

این قالب از فونت‌های زیر استفاده می‌کند:

- `B Nazanin`
- `PGaramond`
- `IranNastaliq`
- `Times New Roman`

اگر یکی از این فونت‌ها روی سیستم نصب نباشد، فرایند XeLaTeX با خطا متوقف
می‌شود.

### ۳. موتور کامپایل

کامپایل باید حتماً با **XeLaTeX** انجام شود.

## نحوه‌ی کامپایل

در پوشه‌ی `thesis/` دستورهای زیر را اجرا کنید:

```bash
xelatex AUTthesis.tex
bibtex AUTthesis
xelatex AUTthesis.tex
xelatex AUTthesis.tex
```

## ساختار فایل‌های مهم

```text
thesis/
├── AUTthesis.tex          ← فایل اصلی فعلی
├── commands.tex           ← تنظیمات بسته‌ها و فونت‌ها
├── fa_title.tex           ← اطلاعات عنوان و چکیده‌ی فارسی
├── chapter1.tex           ← فصل ۱
├── chapter2.tex           ← فصل ۲
├── chapter3.tex           ← فصل ۳
├── chapter4.tex           ← فصل ۴
├── chapter5.tex           ← فصل ۵
├── chapter6.tex           ← فصل ۶
├── appendix1.tex          ← پیوست
├── references.bib         ← منابع
├── dicfa2en.tex           ← واژه‌نامه‌ی فارسی به انگلیسی
└── dicen2fa.tex           ← واژه‌نامه‌ی انگلیسی به فارسی
```

## مواردی که هنوز باید دستی تکمیل شوند

- نام استاد راهنما در `fa_title.tex`
- متن تقدیم در `Chant.tex`
- متن سپاسگزاری در `acknowledgement.tex`
- در صورت نیاز، تاریخ دقیق دفاع یا تحویل
