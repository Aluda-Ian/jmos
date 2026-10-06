{{-- Shared Jeota Media client document styles (matches the Budget Calculator quote/invoice design) --}}
<link rel="icon" type="image/png" href="{{ asset('assets/img/jeota-logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&family=Archivo:wght@600;700;800&display=swap" rel="stylesheet">
<style>
  :root{
    --red:#D62828; --red-deep:#A81E1E; --red-wash:#FBECEC;
    --banner-a:#DA4433; --banner-b:#AE2221; --banner-c:#8B0714;
    --ink:#17161A; --muted:#6E6A66; --faint:#9A958F;
    --line:#EAE7E3; --line-soft:#F1EFEC;
    --paper:#FFFFFF; --panel:#FAF9F7; --panel-2:#F5F3F0;
    --green:#1F8A4C; --green-wash:#EBF6EF;
    --shadow-lg:0 12px 48px rgba(23,22,26,.12);
    --sans:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
    --display:'Space Grotesk','Inter',sans-serif;
    --archivo:'Archivo','Arial Black','Inter',sans-serif;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:var(--sans);color:var(--ink);background:var(--panel-2);line-height:1.45;font-size:14px;-webkit-font-smoothing:antialiased;padding:28px 16px 48px}
  .doc-actions{max-width:820px;margin:0 auto 14px;display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}
  .doc-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 14px;border-radius:8px;font:600 13px var(--sans);border:1px solid var(--line);background:var(--paper);color:var(--ink);cursor:pointer;text-decoration:none}
  .doc-btn:hover{background:var(--panel)}
  .doc-btn svg{width:15px;height:15px}
  .quote-doc{max-width:820px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:var(--shadow-lg);overflow:hidden}
  .q-banner{background:linear-gradient(100deg,var(--banner-a) 0%,var(--banner-b) 52%,var(--banner-c) 100%);color:#fff;padding:34px 44px;display:flex;justify-content:space-between;align-items:flex-start;gap:24px;flex-wrap:wrap}
  .q-banner .co{font-family:var(--archivo);font-weight:800;font-size:34px;letter-spacing:.005em;line-height:1;text-transform:uppercase}
  .q-banner .tag{font-size:11px;letter-spacing:.34em;text-transform:uppercase;margin-top:12px;opacity:.92;font-weight:500}
  .q-banner .right{display:flex;align-items:flex-start;gap:16px}
  .q-banner .contact{text-align:right;font-size:11.5px;line-height:1.85;opacity:.96}
  .q-banner .contact a{color:#fff;text-decoration:none}
  .q-logo{width:78px;height:78px;border-radius:50%;background:#fff;display:grid;place-items:center;flex:0 0 auto;box-shadow:0 4px 14px rgba(0,0,0,.14)}
  .q-logo img{width:52px;height:52px;object-fit:contain}
  .q-body{padding:38px 44px 40px}
  .q-top{display:flex;justify-content:space-between;gap:30px;flex-wrap:wrap}
  .q-top .big{font-family:var(--archivo);font-weight:800;font-size:38px;letter-spacing:.01em;color:var(--ink);line-height:1}
  .q-top .meta-l{font-size:12.5px;color:var(--muted);line-height:2;margin-top:14px;letter-spacing:.04em;text-transform:uppercase}
  .q-top .meta-l b{font-weight:600;color:var(--ink)}
  .q-top .meta-r{text-align:right;font-size:12.5px;color:var(--muted);line-height:1.5}
  .q-top .meta-r .k{letter-spacing:.06em;text-transform:uppercase;font-weight:700;color:var(--faint);font-size:11px}
  .q-top .meta-r .v{font-size:15px;margin:2px 0 4px;display:block;font-weight:600;color:var(--ink)}
  .q-top .meta-r .s{font-size:12px;color:var(--muted);margin-bottom:12px;display:block}
  .status-pill{display:inline-block;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;background:rgba(255,255,255,.2);color:#fff;margin-top:14px}
  .q-table{width:100%;border-collapse:collapse;margin-top:26px}
  .q-table thead th{background:var(--red);color:#fff;text-align:left;font-family:var(--display);font-weight:600;font-size:13px;letter-spacing:.02em;padding:11px 14px}
  .q-table thead th.n{text-align:right} .q-table thead th.c{text-align:center}
  .q-table tbody td{padding:14px;border-bottom:1px solid var(--line-soft);vertical-align:top;font-size:13.5px}
  .q-table .it-name{font-weight:600;color:var(--ink);text-transform:uppercase;letter-spacing:.02em;font-size:12.5px}
  .q-qty{width:60px;text-align:center;color:var(--muted)}
  .q-rate,.q-amt-cell{width:130px;text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
  .q-amt-cell{font-weight:600;color:var(--ink)} .q-rate{color:var(--muted)}
  .q-totals{margin:14px 0 0 auto;width:340px;max-width:100%}
  .q-totals .row{display:flex;justify-content:space-between;padding:6px 0;font-size:13px;gap:12px}
  .q-totals .row .k{color:var(--muted)} .q-totals .row .v{font-variant-numeric:tabular-nums;font-weight:600}
  .q-totals .row.neg .k,.q-totals .row.neg .v{color:var(--red)}
  .q-totals .row.grand{border-top:1.5px solid var(--ink);margin-top:5px;padding-top:10px}
  .q-totals .row.grand .k{font-weight:700;color:var(--ink)} .q-totals .row.grand .v{font-weight:700;font-size:17px;color:var(--red)}
  .q-sections{margin-top:30px;padding-top:22px;border-top:1px solid var(--line);display:grid;grid-template-columns:1fr 1fr;gap:26px}
  .q-sections h5,.q-notes h5{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--red);margin-bottom:9px}
  .pay-line{display:flex;gap:8px;margin:3px 0;font-size:12.5px}
  .pay-line .pk{font-weight:600;color:var(--ink);min-width:78px}
  .pay-line .pv{color:var(--muted)}
  .terms-pay{display:flex;gap:10px;margin-top:6px}
  .terms-pay .cell{flex:1;background:var(--panel);border-radius:8px;padding:9px 11px}
  .terms-pay .cell .k{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--faint)}
  .terms-pay .cell .v{font-weight:700;font-size:13.5px;font-variant-numeric:tabular-nums;margin-top:3px}
  .q-notes{margin-top:24px;padding-top:18px;border-top:1px solid var(--line)}
  .q-notes .txt{font-size:12.5px;color:var(--muted);line-height:1.7}
  .q-foot{margin-top:26px;text-align:center;font-size:11px;color:var(--faint);letter-spacing:.03em;line-height:1.7}
  .q-foot a{color:var(--faint)}
  .notice{border-radius:10px;padding:16px 20px;margin-top:24px;font-size:13.5px;text-align:center;font-weight:600}
  .notice.ok{background:var(--green-wash);color:var(--green);border:1px solid #b9e2c8}
  .approve-bar{margin-top:28px;background:var(--red-wash);border:1px solid #f3c9c9;border-radius:12px;padding:24px;text-align:center}
  .approve-bar h4{font-family:var(--display);font-size:17px;color:var(--ink);margin-bottom:6px}
  .approve-bar p{font-size:13px;color:var(--muted);margin-bottom:16px}
  .btn-approve{display:inline-flex;align-items:center;gap:8px;background:var(--red);color:#fff;font:700 15px var(--sans);padding:14px 32px;border-radius:8px;border:none;cursor:pointer;box-shadow:0 4px 14px rgba(214,40,40,.3)}
  .btn-approve:hover{background:var(--red-deep)}
  .paid-stamp{display:inline-block;border:2.5px solid var(--green);color:var(--green);font-family:var(--archivo);font-weight:800;letter-spacing:.12em;padding:4px 14px;border-radius:6px;transform:rotate(-4deg);margin-top:12px;font-size:18px}
  @media(max-width:640px){
    body{padding:16px 0 32px}
    .quote-doc{border-radius:0}
    .q-banner{padding:24px 20px}
    .q-banner .co{font-size:26px}
    .q-banner .right{width:100%;justify-content:space-between}
    .q-banner .contact{text-align:left}
    .q-body{padding:24px 18px}
    .q-top .big{font-size:30px}
    .q-top .meta-r{text-align:left}
    .q-sections{grid-template-columns:1fr}
    .q-table thead th,.q-table tbody td{padding:10px 8px}
    .q-rate{display:none}
    .doc-actions{padding:0 16px}
  }
  @media print{
    body{background:#fff;padding:0}
    .doc-actions,.approve-bar,form{display:none!important}
    .quote-doc{box-shadow:none;border-radius:0;max-width:100%}
    .q-banner,.q-table thead th{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  }
</style>
