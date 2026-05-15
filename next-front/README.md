# Next.js Application Form

Responsive application form implementation built with Next.js.

## Requirements

- Node.js 20 or newer recommended
- pnpm 10 or newer recommended
- Google Chrome if you want to regenerate Lighthouse reports
- Network access during the first production build, because `next/font` downloads the Exo font from Google Fonts at build time

## Setup

Clone or copy this `next-front` folder onto the target machine, then open a terminal in the project root.

Install dependencies:

```bash
pnpm install
```

Run the development server:

```bash
pnpm dev
```

Open:

```txt
http://localhost:3000
```

If port `3000` is already in use, Next.js may ask to use another port. Use the URL shown in the terminal.

Build the production app:

```bash
pnpm build
```

Run the production server:

```bash
pnpm start
```

Run linting:

```bash
pnpm lint
```

## Verification

1. Open the form at `http://localhost:3000`.
2. Submit the empty form and confirm required-field errors appear for first name, last name, email, and terms agreement.
3. Enter an invalid email and confirm the email validation message appears.
4. Complete all required fields, check the terms checkbox, and submit.
5. Confirm a valid submit does not show validation errors.
6. Resize the browser below `768px` and confirm the layout changes from two columns to one column.

## Architecture

The app uses the Next.js App Router with a single client component for the form UI and validation state.

```txt
app/
  layout.tsx       Root HTML layout and Exo font registration
  page.tsx         Form UI, state management, validation, and submit handler
  globals.css      Global body styles and native input tweaks
public/
  image.png        Form illustration
  calendar.svg     Date input icon
  check.svg        Custom checkbox icon
  arrow-down.svg   Select icon
```

Key implementation details:

- `app/page.tsx` owns form state with React `useState`.
- `validate()` centralizes field validation.
- Required fields are first name, last name, email, and terms agreement.
- The date field uses a native `type="date"` input with an overlay placeholder.
- The checkbox uses an accessible native input with a custom visual label.
- Styling is implemented with Tailwind utility classes in the Next.js version.



## Extending the Form

To add a new field:

1. Add it to the `FormData` interface in `app/page.tsx`.
2. Add the default value to `INITIAL_FORM`.
3. Render the new input in the form grid.
4. If required, add it to `FormErrors` and update `validate()`.
5. Include it in the submit payload if backend submission is added.

Example validation addition:

```ts
if (!data.company.trim()) {
  errors.company = "Company is required";
}
```

To submit to an API instead of logging:

```ts
await fetch("/api/submit", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify(formData),
});
```

the WordPress backend can receive the Next.js submission too, post to:

```txt
http://test-wp.local/wp-json/application-form/v1/submissions
```

That route requires a valid WordPress REST nonce for browser-origin requests in the current plugin implementation.

## Lighthouse

Reports are saved in:

```txt
lighthouse-reports/nextjs.json
lighthouse-reports/nextjs.html
```

Latest generated scores:

```txt
Performance: 100
Accessibility: 80
Best Practices: 93
SEO: 100
```

Regenerate the JSON report:

```bash
npm exec --yes --package lighthouse -- lighthouse http://127.0.0.1:3000 --chrome-path="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --chrome-flags="--headless --no-sandbox --disable-gpu" --output=json --output-path=./lighthouse-reports/nextjs.json --quiet
```

Regenerate the HTML report:

```bash
npm exec --yes --package lighthouse -- lighthouse http://127.0.0.1:3000 --chrome-path="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --chrome-flags="--headless --no-sandbox --disable-gpu" --output=html --output-path=./lighthouse-reports/nextjs.html --quiet
```

On Windows or Linux, replace `--chrome-path` with the Chrome executable path for that machine, or remove it if Lighthouse can find Chrome automatically.
