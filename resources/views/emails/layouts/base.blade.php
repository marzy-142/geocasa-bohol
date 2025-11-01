<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'GeoCasa Bohol')</title>
    <style>
      /* GeoCasa Bohol Brand Colors */
      :root {
        --primary: #0ea5e9;    /* Trust blue (sky-500) */
        --primary-dark: #0284c7; /* sky-600 */
        --accent: #22c55e;     /* Growth green (green-500) */
        --property: #e76f51;   /* Property accent (coral) */
        --success: #22c55e;    /* green-500 */
        --warning: #f59e0b;    /* amber-500 */
        --muted: #737373;      /* neutral-500 */
        --bg: #fafafa;         /* neutral-50 */
        --fg: #171717;         /* neutral-900 */
        --card: #ffffff;
        --border: #e5e5e5;     /* neutral-200 */
      }
      body { margin:0; padding:24px; background:var(--bg); font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color:var(--fg); line-height:1.6; }
      .container { max-width:600px; margin:0 auto; background:var(--card); border-radius:12px; overflow:hidden; border:1px solid var(--border); box-shadow:0 4px 12px rgba(0,0,0,0.08); }
      .header { padding:24px; background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); display:flex; align-items:center; gap:12px; }
      .brand { font-size:20px; font-weight:700; letter-spacing:-0.025em; color:#ffffff; }
      .logo { height:32px; width:auto; display:block; filter:brightness(0) invert(1); }
      .content { padding:32px 24px; color:var(--fg); }
      .footer { padding:20px 24px; border-top:1px solid var(--border); color:var(--muted); font-size:13px; background:#fafafa; text-align:center; }
      .card { background:#f9fafb; border:1px solid var(--border); border-radius:10px; padding:18px; margin:16px 0; }
      .btn { display:inline-block; background:var(--primary); color:#fff !important; text-decoration:none; padding:12px 24px; border-radius:8px; font-weight:600; font-size:15px; box-shadow:0 2px 4px rgba(14,165,233,0.2); }
      .btn:hover { background:var(--primary-dark); }
      .muted { color:var(--muted); font-size:14px; }
      h1 { color:var(--fg); font-size:24px; font-weight:700; margin:0 0 8px 0; letter-spacing:-0.025em; }
      h2 { color:var(--fg); font-size:20px; font-weight:600; margin:24px 0 12px 0; letter-spacing:-0.025em; }
      h3 { color:var(--fg); font-size:18px; font-weight:600; margin:20px 0 10px 0; letter-spacing:-0.025em; }
      h4 { color:var(--fg); font-size:16px; font-weight:600; margin:16px 0 8px 0; }
      p { margin:12px 0; line-height:1.6; }
      @media (max-width:640px){ .content{padding:24px 16px;} .header{padding:20px 16px;} .footer{padding:16px;} }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="header">
        <!-- Logo: place your file at public/images/logo.png -->
        <img class="logo" src="{{ url('images/logo.png') }}" alt="GeoCasa Bohol" />
        <div class="brand">GeoCasa Bohol</div>
      </div>
      <div class="content">
        @yield('content')
      </div>
      <div class="footer">
        © {{ date('Y') }} GeoCasa Bohol. All rights reserved.
      </div>
    </div>
  </body>
</html>