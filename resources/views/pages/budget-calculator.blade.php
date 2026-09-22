<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JMOS — Production Budget Calculator</title>

<!-- Favicon -->
<link rel="icon" type="image/png" href="{{ asset('assets/img/jeota-logo.png') }}">
<link rel="shortcut icon" type="image/png" href="{{ asset('assets/img/jeota-logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('assets/img/jeota-logo.png') }}">
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
    --shadow:0 1px 2px rgba(23,22,26,.04),0 8px 24px rgba(23,22,26,.06);
    --shadow-lg:0 12px 48px rgba(23,22,26,.16);
    --r:12px; --r-sm:8px;
    --sans:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
    --display:'Space Grotesk','Inter',sans-serif;
    --archivo:'Archivo','Arial Black','Inter',sans-serif;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:var(--sans);color:var(--ink);background:var(--panel-2);line-height:1.45;font-size:14px;-webkit-font-smoothing:antialiased}
  .tnum{font-variant-numeric:tabular-nums;font-feature-settings:"tnum" 1}

  /* ---------- Top bar ---------- */
  .topbar{position:sticky;top:0;z-index:40;background:var(--paper);border-bottom:1px solid var(--line);display:flex;align-items:center;gap:20px;padding:13px 22px;flex-wrap:wrap}
  .brand{display:flex;align-items:center;gap:11px}
  .brand .mark{width:36px;height:36px;border-radius:50%;background:var(--red-wash);display:grid;place-items:center;overflow:hidden}
  .brand .mark img{width:26px;height:26px;object-fit:contain}
  .brand .word{font-family:var(--display);font-weight:700;font-size:16px;letter-spacing:.01em;line-height:1}
  .brand .word .r{color:var(--red)} .brand .word .m{color:var(--ink);font-weight:600;opacity:.6}
  .brand .sub{font-size:10.5px;color:var(--faint);letter-spacing:.12em;text-transform:uppercase;margin-top:3px}
  .topbar .divider{width:1px;height:30px;background:var(--line)}
  .module-name{font-family:var(--display);font-weight:600;font-size:15px}
  .topbar .spacer{flex:1}
  .toolbar{display:flex;gap:8px;flex-wrap:wrap}
  button{font-family:var(--sans);cursor:pointer;border:none;background:none;color:inherit}
  .btn{display:inline-flex;align-items:center;gap:7px;padding:9px 14px;border-radius:var(--r-sm);font-size:13px;font-weight:600;border:1px solid var(--line);background:var(--paper);color:var(--ink);transition:background .14s,border-color .14s,transform .05s}
  .btn:hover{background:var(--panel);border-color:#dcd8d3}
  .btn:active{transform:translateY(1px)}
  .btn svg{width:15px;height:15px;stroke-width:2}
  .btn-primary{background:var(--red);color:#fff;border-color:var(--red)}
  .btn-primary:hover{background:var(--red-deep);border-color:var(--red-deep)}
  .btn-ghost{border-color:transparent;background:transparent;color:var(--muted)}
  .btn-ghost:hover{background:var(--panel-2);color:var(--ink)}

  /* ---------- Meta ---------- */
  .meta{max-width:1240px;margin:22px auto 0;padding:0 22px;display:flex;gap:18px;flex-wrap:wrap;align-items:flex-end}
  .field{display:flex;flex-direction:column;gap:5px}
  .field label{font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--faint)}
  .field input,.field select{font-family:var(--sans);font-size:14px;color:var(--ink);padding:9px 11px;border:1px solid var(--line);border-radius:var(--r-sm);background:var(--paper);min-width:180px;transition:border-color .14s,box-shadow .14s}
  .field input:focus,.field select:focus{outline:none;border-color:var(--red);box-shadow:0 0 0 3px var(--red-wash)}
  .field.grow{flex:1 1 240px} .field.grow input{width:100%}

  /* ---------- Layout ---------- */
  .wrap{max-width:1240px;margin:0 auto;padding:20px 22px 230px}
  .columns{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:18px}
  @media(max-width:900px){.columns{grid-template-columns:1fr}}
  .col-head{display:flex;align-items:center;gap:9px;margin:6px 2px 12px}
  .col-head .tag{font-family:var(--display);font-weight:700;font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:var(--red)}
  .col-head .tag.exp{color:var(--muted)}
  .col-head .rule{flex:1;height:1px;background:var(--line)}

  .card{background:var(--paper);border:1px solid var(--line);border-radius:var(--r);box-shadow:var(--shadow);margin-bottom:16px;overflow:hidden}
  .card-head{display:flex;align-items:baseline;justify-content:space-between;gap:10px;padding:14px 16px 10px}
  .card-head h3{font-family:var(--display);font-weight:600;font-size:14.5px}
  .card-head .sum{font-weight:700;font-size:14px}
  .card-head .sum .cur{color:var(--faint);font-weight:600;font-size:11px;margin-right:2px}
  table{width:100%;border-collapse:collapse}
  thead th{font-size:10.5px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--faint);text-align:left;padding:6px 10px;border-bottom:1px solid var(--line-soft)}
  thead th.n{text-align:right}
  tbody td{padding:2px 6px;border-bottom:1px solid var(--line-soft);vertical-align:middle}
  tbody tr:last-child td{border-bottom:none}
  .cell-input{width:100%;border:1px solid transparent;background:transparent;font-family:var(--sans);font-size:13.5px;color:var(--ink);padding:8px;border-radius:6px;transition:background .12s,border-color .12s}
  .cell-input:hover{background:var(--panel)}
  .cell-input:focus{outline:none;background:var(--paper);border-color:var(--red);box-shadow:0 0 0 3px var(--red-wash)}
  td.num .cell-input{text-align:right;font-variant-numeric:tabular-nums}
  .col-name{width:42%} .col-rate,.col-qty{width:19%}
  .col-total{width:16%;text-align:right;padding-right:12px!important;font-weight:600;font-variant-numeric:tabular-nums;white-space:nowrap}
  .col-x{width:34px;text-align:center}
  .rowdel{width:24px;height:24px;border-radius:6px;display:grid;place-items:center;color:var(--faint);opacity:0;transition:opacity .12s,background .12s,color .12s}
  tr:hover .rowdel{opacity:1}
  .rowdel:hover{background:var(--red-wash);color:var(--red)}
  .rowdel svg{width:14px;height:14px;stroke-width:2}
  .add-row{display:flex;align-items:center;gap:6px;width:100%;padding:9px 16px;font-size:12.5px;font-weight:600;color:var(--muted);border-top:1px solid var(--line-soft);background:var(--panel);transition:background .12s,color .12s}
  .add-row:hover{background:var(--panel-2);color:var(--red)}
  .add-row svg{width:14px;height:14px;stroke-width:2.2}

  /* ---------- Dock ---------- */
  .dock{position:fixed;left:0;right:0;bottom:0;z-index:35;background:var(--paper);border-top:1px solid var(--line);box-shadow:0 -8px 32px rgba(23,22,26,.09)}
  .dock-inner{max-width:1240px;margin:0 auto;padding:16px 22px;display:grid;grid-template-columns:1.15fr 1fr 1.05fr;gap:26px;align-items:center}
  @media(max-width:900px){.dock-inner{grid-template-columns:1fr;gap:14px;padding:14px 18px}.dock{max-height:62vh;overflow:auto}}
  .dock-block h4{font-size:10.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--faint);margin-bottom:10px}
  .markup-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:9px}
  .markup-val{font-family:var(--display);font-weight:700;font-size:22px;color:var(--red)}
  .presets{display:flex;gap:6px}
  .preset{padding:5px 11px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid var(--line);background:var(--paper);color:var(--muted)}
  .preset:hover{border-color:var(--red);color:var(--red)}
  .preset.on{background:var(--red);border-color:var(--red);color:#fff}
  .slider-wrap{position:relative;padding-top:2px}
  .band{position:absolute;top:2px;height:6px;background:var(--red-wash);border-radius:6px;pointer-events:none;left:40%;width:20%}
  input[type=range]{-webkit-appearance:none;appearance:none;width:100%;height:6px;border-radius:6px;background:var(--line);cursor:pointer;position:relative}
  input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:18px;height:18px;border-radius:50%;background:var(--red);border:3px solid #fff;box-shadow:0 1px 4px rgba(214,40,40,.5);cursor:pointer}
  input[type=range]::-moz-range-thumb{width:14px;height:14px;border-radius:50%;background:var(--red);border:3px solid #fff;box-shadow:0 1px 4px rgba(214,40,40,.5);cursor:pointer}
  .band-label{display:flex;justify-content:space-between;font-size:10.5px;color:var(--faint);margin-top:6px}
  .margin-note{font-size:12px;color:var(--muted);margin-top:9px}
  .margin-note b{color:var(--green);font-weight:700}
  .lines{display:flex;flex-direction:column;gap:7px}
  .line{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:13px}
  .line .lbl{color:var(--muted);display:flex;align-items:center;gap:7px}
  .line .amt{font-weight:600;font-variant-numeric:tabular-nums;white-space:nowrap}
  .line.profit .lbl{color:var(--ink);font-weight:600} .line.profit .amt{color:var(--green);font-weight:700}
  .line.muted .lbl,.line.muted .amt{color:var(--faint)}
  .line.wht .amt{color:var(--red)}
  .line-div{height:1px;background:var(--line);margin:3px 0}
  .toggle{display:inline-flex;align-items:center;gap:8px;cursor:pointer;user-select:none}
  .switch{width:34px;height:20px;border-radius:20px;background:var(--line);position:relative;transition:background .16s;flex:0 0 auto}
  .switch::after{content:"";position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:transform .16s}
  .toggle.on .switch{background:var(--red)}
  .toggle.on .switch::after{transform:translateX(14px)}
  .info{width:15px;height:15px;border-radius:50%;border:1px solid var(--faint);color:var(--faint);font-size:10px;font-weight:700;display:inline-grid;place-items:center;cursor:help}
  .final{background:var(--panel);border:1px solid var(--line);border-radius:var(--r);padding:14px 16px}
  .final .big{display:flex;align-items:baseline;justify-content:space-between;gap:10px;margin-bottom:4px}
  .final .big .k{font-size:12px;font-weight:600;color:var(--muted)}
  .final .big .v{font-family:var(--display);font-weight:700;font-size:26px;font-variant-numeric:tabular-nums}
  .final .split{display:flex;gap:10px;margin-top:12px;padding-top:12px;border-top:1px dashed var(--line)}
  .split-cell{flex:1;background:var(--paper);border:1px solid var(--line);border-radius:var(--r-sm);padding:9px 11px}
  .split-cell .k{font-size:10.5px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:var(--faint);display:flex;gap:5px;align-items:center}
  .split-cell .v{font-weight:700;font-size:15px;font-variant-numeric:tabular-nums;margin-top:3px}
  .dep-input{width:38px;border:none;background:var(--red-wash);color:var(--red);font-weight:700;font-size:11px;text-align:center;border-radius:4px;padding:1px 2px;font-family:var(--sans)}
  .dep-input:focus{outline:1px solid var(--red)}
  .hint{font-size:11.5px;color:var(--faint);margin-top:8px;line-height:1.5}

  /* ---------- Quote overlay ---------- */
  .quote-overlay{position:fixed;inset:0;z-index:60;background:rgba(23,22,26,.6);display:none;overflow:auto;padding:28px 16px}
  .quote-overlay.show{display:block}
  .quote-bar{max-width:820px;margin:0 auto 16px;display:flex;gap:10px;justify-content:space-between;align-items:center;flex-wrap:wrap}
  .quote-bar .ttl{color:#fff;font-size:13px;opacity:.9}
  .quote-bar .grp{display:flex;gap:8px}
  .quote-doc{max-width:820px;margin:0 auto;background:#fff;border-radius:var(--r);box-shadow:var(--shadow-lg);overflow:hidden}

  .q-banner{background:linear-gradient(100deg,var(--banner-a) 0%,var(--banner-b) 52%,var(--banner-c) 100%);color:#fff;padding:34px 44px;display:flex;justify-content:space-between;align-items:flex-start;gap:24px}
  .q-banner .co{font-family:var(--archivo);font-weight:800;font-size:34px;letter-spacing:.005em;line-height:1;text-transform:uppercase}
  .q-banner .tag{font-size:11px;letter-spacing:.34em;text-transform:uppercase;margin-top:12px;opacity:.92;font-weight:500}
  .q-banner .right{display:flex;align-items:flex-start;gap:16px}
  .q-banner .contact{text-align:right;font-size:11.5px;line-height:1.85;opacity:.96}
  .q-banner .contact a{color:#fff;text-decoration:none}
  .q-logo{width:78px;height:78px;border-radius:50%;background:#fff;display:grid;place-items:center;flex:0 0 auto;margin-left:14px;box-shadow:0 4px 14px rgba(0,0,0,.14)}
  .q-logo img{width:52px;height:52px;object-fit:contain}

  .q-body{padding:38px 44px 40px}
  .q-top{display:flex;justify-content:space-between;gap:30px;flex-wrap:wrap}
  .q-top .big{font-family:var(--archivo);font-weight:800;font-size:38px;letter-spacing:.01em;color:var(--ink);line-height:1}
  .q-top .meta-l{font-size:12.5px;color:var(--muted);line-height:2;margin-top:14px;letter-spacing:.04em;text-transform:uppercase}
  .q-top .meta-l .qedit,.q-top .meta-r .v{font-weight:600;color:var(--ink)}
  .q-top .meta-r{text-align:right;font-size:12.5px;color:var(--muted);line-height:1.5}
  .q-top .meta-r .k{letter-spacing:.06em;text-transform:uppercase;font-weight:700;color:var(--faint);font-size:11px}
  .q-top .meta-r .v{font-size:15px;margin:2px 0 14px;display:block}

  .qedit{border:1px dashed transparent;border-radius:4px;padding:0 2px;transition:border-color .12s,background .12s}
  .qedit:hover{border-color:var(--line)}
  .qedit:focus{outline:none;border-color:var(--red);background:var(--panel)}
  .qi{font-family:inherit;font-size:inherit;color:inherit;font-weight:inherit;border:1px dashed transparent;background:transparent;border-radius:4px;padding:0 2px}
  .qi:hover{border-color:var(--line)} .qi:focus{outline:none;border-color:var(--red);background:var(--panel)}

  .q-table{width:100%;border-collapse:collapse;margin-top:26px}
  .q-table thead th{background:var(--red);color:#fff;text-align:left;font-family:var(--display);font-weight:600;font-size:13px;letter-spacing:.02em;text-transform:none;padding:11px 14px;border:none}
  .q-table thead th.n{text-align:right}
  .q-table tbody td{padding:14px;border-bottom:1px solid var(--line-soft);vertical-align:top;font-size:13.5px}
  .q-table .it-name{font-weight:600;color:var(--ink);text-transform:uppercase;letter-spacing:.02em;font-size:12.5px;width:150px}
  .q-deliv{list-style:none;margin:0;padding:0}
  .q-deliv li{position:relative;padding-left:15px;margin:2px 0;color:var(--muted);font-size:13px;line-height:1.55}
  .q-deliv li::before{content:"";position:absolute;left:2px;top:8px;width:4px;height:4px;border-radius:50%;background:var(--red)}
  .q-deliv:focus{outline:none}
  .q-deliv[contenteditable]:focus{box-shadow:0 0 0 2px var(--red-wash);border-radius:6px}
  .q-qty{width:52px;text-align:center;color:var(--muted)}
  .q-amt-cell{width:120px;text-align:right;font-weight:600;font-variant-numeric:tabular-nums;color:var(--ink);white-space:nowrap}
  .q-del{width:26px;text-align:center}
  .q-del button{width:22px;height:22px;border-radius:5px;display:grid;place-items:center;color:var(--faint);opacity:0}
  .q-table tbody tr:hover .q-del button{opacity:1}
  .q-del button:hover{background:var(--red-wash);color:var(--red)}
  .q-del svg{width:13px;height:13px;stroke-width:2}
  .q-total-row td{border-top:2px solid var(--ink);border-bottom:none!important;padding-top:12px!important}
  .q-total-row .lbl{font-family:var(--display);font-weight:700;font-size:14px;text-transform:uppercase;letter-spacing:.04em}
  .q-total-row .val{text-align:right;font-family:var(--display);font-weight:700;font-size:16px;font-variant-numeric:tabular-nums}
  .q-additem{display:inline-flex;align-items:center;gap:6px;margin-top:10px;font-size:12.5px;font-weight:600;color:var(--muted);padding:6px 10px;border-radius:6px}
  .q-additem:hover{color:var(--red);background:var(--red-wash)}
  .q-additem svg{width:14px;height:14px;stroke-width:2.2}

  .q-totals{margin:14px 0 0 auto;width:320px}
  .q-totals .row{display:flex;justify-content:space-between;padding:6px 0;font-size:13px}
  .q-totals .row .k{color:var(--muted)} .q-totals .row .v{font-variant-numeric:tabular-nums;font-weight:600}
  .q-totals .row.wht .k,.q-totals .row.wht .v{color:var(--red)}
  .q-totals .row.grand{border-top:1.5px solid var(--ink);margin-top:5px;padding-top:10px}
  .q-totals .row.grand .k{font-weight:700;color:var(--ink)} .q-totals .row.grand .v{font-weight:700;font-size:16px;color:var(--red)}

  .wht-callout{background:var(--red-wash);border-radius:8px;padding:11px 13px;font-size:12px;color:var(--red-deep);margin-top:16px;line-height:1.55}
  .wht-callout b{font-weight:700}

  .q-sections{margin-top:30px;padding-top:22px;border-top:1px solid var(--line);display:grid;grid-template-columns:1fr 1fr;gap:26px}
  @media(max-width:640px){.q-sections{grid-template-columns:1fr}}
  .q-sections h5{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--red);margin-bottom:9px}
  .q-sections p,.q-sections .qedit{font-size:12.5px;color:var(--muted);line-height:1.7}
  .pay-line{display:flex;gap:8px;margin:3px 0;font-size:12.5px}
  .pay-line .pk{font-weight:600;color:var(--ink);min-width:70px}
  .pay-line .pv{color:var(--muted)}
  .terms-pay{display:flex;gap:10px;margin-top:6px}
  .terms-pay .cell{flex:1;background:var(--panel);border-radius:8px;padding:9px 11px}
  .terms-pay .cell .k{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--faint)}
  .terms-pay .cell .v{font-weight:700;font-size:14px;font-variant-numeric:tabular-nums;margin-top:3px}
  .q-notes{margin-top:24px;padding-top:18px;border-top:1px solid var(--line)}
  .q-notes h5{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--faint);margin-bottom:8px}
  .q-notes ul{list-style:none;padding:0;margin:0}
  .q-notes li{position:relative;padding-left:14px;font-size:12px;color:var(--muted);line-height:1.7}
  .q-notes li::before{content:"·";position:absolute;left:3px;color:var(--faint);font-weight:700}
  .q-foot{margin-top:26px;text-align:center;font-size:11px;color:var(--faint);letter-spacing:.03em}

  .toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--ink);color:#fff;padding:11px 18px;border-radius:10px;font-size:13px;font-weight:500;box-shadow:var(--shadow-lg);opacity:0;pointer-events:none;transition:opacity .2s,transform .2s;z-index:80}
  .toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
  @media (prefers-reduced-motion:reduce){*{transition:none!important}}
  input:focus-visible,button:focus-visible{outline:2px solid var(--red);outline-offset:1px}

  @media print{
    body{background:#fff}
    .topbar,.meta,.wrap,.dock,.quote-bar,.q-del,.q-additem{display:none!important}
    .quote-overlay{position:static;background:#fff;padding:0;display:block!important;overflow:visible}
    .quote-doc{box-shadow:none;max-width:100%;margin:0;border-radius:0}
    .qedit:hover,.qi:hover,.q-deliv[contenteditable]:focus{border-color:transparent!important;background:transparent!important;box-shadow:none!important}
    @page{margin:0}
  }
</style>
</head>
<body>

<header class="topbar">
  <div class="brand">
    <div class="mark"><img src="../assets/img/jeota-logo.png" alt="Jeota"></div>
    <div>
      <div class="word"><span class="r">JEOTA</span> <span class="m">MEDIA LTD</span></div>
      <div class="sub">JMOS · Budget</div>
    </div>
  </div>
  <div class="divider"></div>
  <div class="module-name">Production Budget Calculator</div>
  <div class="spacer"></div>
  <div class="toolbar">
    <button class="btn btn-ghost" onclick="newBudget()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>New</button>
    <button class="btn" onclick="exportJSON()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>Save</button>
    <button class="btn" onclick="document.getElementById('importer').click()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 8l5-5 5 5M12 3v12"/></svg>Open</button>
    <input type="file" id="importer" accept="application/json" style="display:none" onchange="importJSON(event)">
    <button class="btn btn-primary" onclick="openQuote()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 13h6M9 17h4"/></svg>Generate quote</button>
  </div>
</header>

<div class="meta">
  <div class="field grow"><label>Project</label><input id="m-project" value="Moyo Honey — Brand Film" oninput="saveMeta()"></div>
  <div class="field grow"><label>Client</label><input id="m-client" value="Moyo Honey Ltd" oninput="saveMeta()"></div>
  <div class="field"><label>Currency</label><select id="m-currency" onchange="setCurrency()"><option value="KES">KES — Kenyan Shilling</option><option value="USD">USD — US Dollar</option></select></div>
</div>

<div class="wrap">
  <div class="columns">
    <div>
      <div class="col-head"><span class="tag">Fees · Crew &amp; Talent</span><span class="rule"></span></div>
      <div id="sec-production"></div><div id="sec-post"></div><div id="sec-talent"></div>
    </div>
    <div>
      <div class="col-head"><span class="tag exp">Expenses</span><span class="rule"></span></div>
      <div id="sec-equipment"></div><div id="sec-prodexp"></div><div id="sec-travel"></div>
    </div>
  </div>
</div>

<div class="dock">
  <div class="dock-inner">
    <div class="dock-block">
      <h4>Markup on cost</h4>
      <div class="markup-row"><span class="markup-val"><span id="markup-num">40</span>%</span>
        <div class="presets"><button class="preset" data-m="40" onclick="setMarkup(40)">40</button><button class="preset" data-m="50" onclick="setMarkup(50)">50</button><button class="preset" data-m="60" onclick="setMarkup(60)">60</button></div>
      </div>
      <div class="slider-wrap"><div class="band"></div><input type="range" id="markup" min="0" max="100" value="40" oninput="setMarkup(this.value)"></div>
      <div class="band-label"><span>0%</span><span style="color:var(--red)">target 40–60%</span><span>100%</span></div>
      <div class="margin-note">That's a profit margin of <b id="margin-pct">28.6%</b> on the price.</div>
    </div>
    <div class="dock-block">
      <h4>Breakdown</h4>
      <div class="lines">
        <div class="line cost"><span class="lbl">Total production cost</span><span class="amt" id="o-cost">—</span></div>
        <div class="line profit"><span class="lbl">Your profit</span><span class="amt" id="o-profit">—</span></div>
        <div class="line-div"></div>
        <div class="line"><span class="lbl">Price to client (fee)</span><span class="amt" id="o-fee">—</span></div>
        <div class="line muted" id="row-vat"><span class="lbl"><label class="toggle" id="tg-vat" onclick="toggleVat()"><span class="switch"></span>VAT (0% Service Exempt)</label></span><span class="amt" id="o-vat">—</span></div>
        <div class="line wht" id="row-wht"><span class="lbl"><label class="toggle" id="tg-wht" onclick="toggleWht()"><span class="switch"></span>eTIMS · less 5% WHT</label><span class="info" title="For clients who issue eTIMS invoices and must withhold tax. They deduct 5% and remit it to KRA on Jeota's behalf. You reclaim it with the withholding-tax certificate, so your profit is unchanged.">i</span></span><span class="amt" id="o-wht">—</span></div>
      </div>
    </div>
    <div class="dock-block">
      <div class="final">
        <div class="big"><span class="k" id="k-final">Total to client</span><span class="v" id="o-final">—</span></div>
        <div class="hint" id="net-note" style="display:none">After 5% withholding, this is what the client pays you now.</div>
        <div class="split">
          <div class="split-cell"><div class="k">Deposit <input class="dep-input" id="dep-pct" value="60" oninput="setDeposit(this.value)">%</div><div class="v" id="o-deposit">—</div></div>
          <div class="split-cell"><div class="k">Balance <span id="bal-pct" style="color:var(--faint)">40</span>%</div><div class="v" id="o-balance">—</div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="quote-overlay" id="quote-overlay">
  <div class="quote-bar">
    <span class="ttl">Quote preview — edit fields, then print, email, or save as PDF</span>
    <div class="grp">
      <button class="btn" onclick="closeQuote()">Back</button>
      <button class="btn" onclick="resetQuoteFromBudget()" title="Replace items with one line at the budget total">Reset from budget</button>
      <button class="btn" onclick="sendQuoteEmail()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Send to mail</button>
      <button class="btn btn-primary" onclick="window.print()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>Print / Save PDF</button>
    </div>
  </div>
  <div class="quote-doc" id="quote-doc"></div>
</div>

<div class="toast" id="toast"></div>

<script>
const LOGO="../assets/img/jeota-logo.png";

const SECTIONS={
  production:{el:'sec-production',title:'Production Crew',cols:['Role','Day rate','Days']},
  post:{el:'sec-post',title:'Post-Production',cols:['Role','Day rate','Days']},
  talent:{el:'sec-talent',title:'Talent',cols:['Role','Rate','Qty']},
  equipment:{el:'sec-equipment',title:'Equipment',cols:['Item','Day rate','Days']},
  prodexp:{el:'sec-prodexp',title:'Production Expenses',cols:['Item','Cost','Qty']},
  travel:{el:'sec-travel',title:'Travel & Logistics',cols:['Item','Cost','Qty']},
};

function today(){return new Date().toISOString().slice(0,10);}
function plusDays(iso,d){const dt=new Date(iso);dt.setDate(dt.getDate()+d);return dt.toISOString().slice(0,10);}
function prettyDate(iso){const dt=new Date(iso);if(isNaN(dt))return iso;return dt.toLocaleDateString('en-GB',{day:'2-digit',month:'long',year:'numeric'}).toUpperCase();}

function seed(){
  return {
    meta:{project:'Moyo Honey — Brand Film',client:'Moyo Honey Ltd',currency:'KES',quoteNo:'JM-Q-0001',date:today()},
    pricing:{markup:40,vat:false,wht:false,deposit:60},
    contact:{email:'info@jeotamedia.co.ke',phone:'+254 791 388 683',web:'www.jeotamedia.co.ke',addr1:'Keystone Park',addr2:'Nairobi, Kenya'},
    quote:{items:null,attn:'',mpesa:'Paybill 880100 · Acc: 352655',bank:'NCBA — Junction Branch · A/C: Jeota Media Limited · A/C No. 6237790012',cheque:'Cheques should be made payable to Jeota Media Limited.',
      notes:['All prices are exclusive of any applicable taxes unless otherwise stated.','Client to provide any existing brand assets (logo files, brand guidelines, fonts) before production begins.']},
    data:{
      production:[{name:'Director',rate:0,qty:1},{name:'Camera Operator',rate:10000,qty:1},{name:'2nd Camera Operator',rate:10000,qty:1},{name:'Photographer',rate:10000,qty:1}],
      post:[{name:'Editor',rate:10000,qty:1}],
      talent:[{name:'Voice over',rate:5000,qty:1}],
      equipment:[{name:'Sony A7 IV',rate:6000,qty:2},{name:'Sony A6700',rate:0,qty:1},{name:'Canon 250D',rate:0,qty:1},{name:'Sigma 24-70',rate:0,qty:1},{name:'Rode mics',rate:0,qty:1},{name:'Godox lights',rate:0,qty:1}],
      prodexp:[{name:'Location permit',rate:0,qty:1},{name:'Props / supplies',rate:0,qty:1},{name:'Music licensing',rate:0,qty:1},{name:'Storage (1TB drive)',rate:0,qty:1}],
      travel:[{name:'Rental car',rate:0,qty:1},{name:'Fuel',rate:0,qty:1}],
    }
  };
}
let S=seed();

const CUR={KES:{sym:'KES',dp:0},USD:{sym:'$',dp:2}};
function fmt(n){const c=CUR[S.meta.currency]||CUR.KES;const v=isFinite(n)?n:0;return c.sym+' '+v.toLocaleString('en-KE',{minimumFractionDigits:c.dp,maximumFractionDigits:c.dp});}
function fmtn(n){const c=CUR[S.meta.currency]||CUR.KES;return (isFinite(n)?n:0).toLocaleString('en-KE',{minimumFractionDigits:c.dp,maximumFractionDigits:c.dp});}
function num(v){const n=parseFloat(String(v).replace(/[^0-9.\-]/g,''));return isFinite(n)?n:0;}

function sectionTotal(k){return S.data[k].reduce((s,r)=>s+num(r.rate)*num(r.qty),0);}
function totalCost(){return Object.keys(SECTIONS).reduce((s,k)=>s+sectionTotal(k),0);}
function calc(){
  const cost=totalCost(),m=S.pricing.markup/100,profit=cost*m,fee=cost+profit;
  const vat=S.pricing.vat?fee*0.16:0,wht=S.pricing.wht?fee*0.05:0,gross=fee+vat,net=gross-wht;
  const dep=net*(S.pricing.deposit/100),bal=net-dep,margin=fee>0?profit/fee*100:0;
  return {cost,profit,fee,vat,wht,gross,net,dep,bal,margin};
}

/* ---- render budget sections ---- */
function renderSection(key){
  const cfg=SECTIONS[key],rows=S.data[key];let body='';
  rows.forEach((r,i)=>{
    const total=num(r.rate)*num(r.qty);
    body+=`<tr>
      <td class="col-name"><input class="cell-input" value="${esc(r.name)}" placeholder="Add a line…" oninput="upd('${key}',${i},'name',this.value)"></td>
      <td class="col-rate num"><input class="cell-input tnum" inputmode="decimal" value="${r.rate||''}" placeholder="0" oninput="upd('${key}',${i},'rate',this.value)"></td>
      <td class="col-qty num"><input class="cell-input tnum" inputmode="decimal" value="${r.qty}" oninput="upd('${key}',${i},'qty',this.value)"></td>
      <td class="col-total" data-total="${key}-${i}">${fmt(total)}</td>
      <td class="col-x"><button class="rowdel" title="Remove" onclick="delRow('${key}',${i})"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 6L6 18M6 6l12 12"/></svg></button></td>
    </tr>`;
  });
  document.getElementById(cfg.el).innerHTML=`<div class="card"><div class="card-head"><h3>${cfg.title}</h3>
    <span class="sum"><span class="cur">${CUR[S.meta.currency].sym}</span><span data-sumval="${key}">${fmtn(sectionTotal(key))}</span></span></div>
    <table><thead><tr><th>${cfg.cols[0]}</th><th class="n">${cfg.cols[1]}</th><th class="n">${cfg.cols[2]}</th><th class="n">Total</th><th></th></tr></thead>
    <tbody>${body}</tbody></table>
    <button class="add-row" onclick="addRow('${key}')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>Add line</button></div>`;
}
function renderAll(){Object.keys(SECTIONS).forEach(renderSection);recompute();}
function upd(key,i,field,val){
  S.data[key][i][field]=val;
  const r=S.data[key][i],t=num(r.rate)*num(r.qty);
  const c=document.querySelector(`[data-total="${key}-${i}"]`);if(c)c.textContent=fmt(t);
  const sv=document.querySelector(`[data-sumval="${key}"]`);if(sv)sv.textContent=fmtn(sectionTotal(key));
  recompute();
}
function addRow(key){S.data[key].push({name:'',rate:0,qty:1});renderSection(key);recompute();
  const inp=document.querySelectorAll(`#${SECTIONS[key].el} .col-name input`);if(inp.length)inp[inp.length-1].focus();}
function delRow(key,i){S.data[key].splice(i,1);renderSection(key);recompute();}

/* ---- pricing controls ---- */
function setMarkup(v){v=Math.round(num(v));S.pricing.markup=v;document.getElementById('markup').value=v;document.getElementById('markup-num').textContent=v;
  document.querySelectorAll('.preset').forEach(p=>p.classList.toggle('on',+p.dataset.m===v));recompute();}
function toggleVat(){S.pricing.vat=!S.pricing.vat;document.getElementById('tg-vat').classList.toggle('on',S.pricing.vat);recompute();}
function toggleWht(){S.pricing.wht=!S.pricing.wht;document.getElementById('tg-wht').classList.toggle('on',S.pricing.wht);recompute();}
function setDeposit(v){let d=Math.min(100,Math.max(0,Math.round(num(v))));S.pricing.deposit=d;document.getElementById('bal-pct').textContent=100-d;recompute();}

function recompute(){
  const c=calc();
  document.getElementById('o-cost').textContent=fmt(c.cost);
  document.getElementById('o-profit').textContent='+ '+fmt(c.profit);
  document.getElementById('o-fee').textContent=fmt(c.fee);
  document.getElementById('margin-pct').textContent=c.margin.toFixed(1)+'%';
  document.getElementById('o-vat').textContent=S.pricing.vat?'+ '+fmt(c.vat):'off';
  document.getElementById('row-vat').classList.toggle('muted',!S.pricing.vat);
  document.getElementById('o-wht').textContent=S.pricing.wht?'− '+fmt(c.wht):'off';
  document.getElementById('row-wht').classList.toggle('muted',!S.pricing.wht);
  document.getElementById('o-final').textContent=fmt(c.net);
  document.getElementById('k-final').textContent=S.pricing.wht?'Client pays now':'Total to client';
  document.getElementById('net-note').style.display=S.pricing.wht?'block':'none';
  document.getElementById('o-deposit').textContent=fmt(c.dep);
  document.getElementById('o-balance').textContent=fmt(c.bal);
}

/* ---- meta ---- */
function saveMeta(){S.meta.project=document.getElementById('m-project').value;S.meta.client=document.getElementById('m-client').value;}
function setCurrency(){S.meta.currency=document.getElementById('m-currency').value;renderAll();}

/* ---- new / save / open ---- */
function newBudget(){
  if(!confirm('Clear this budget and start a new one?'))return;
  S=seed();S.data={production:[{name:'',rate:0,qty:1}],post:[{name:'',rate:0,qty:1}],talent:[{name:'',rate:0,qty:1}],equipment:[{name:'',rate:0,qty:1}],prodexp:[{name:'',rate:0,qty:1}],travel:[{name:'',rate:0,qty:1}]};
  S.quote.items=null;syncInputs();setMarkup(40);renderAll();toast('New budget started');
}
function syncInputs(){
  document.getElementById('m-project').value=S.meta.project;
  document.getElementById('m-client').value=S.meta.client;
  document.getElementById('m-currency').value=S.meta.currency;
  document.getElementById('dep-pct').value=S.pricing.deposit;
  document.getElementById('bal-pct').textContent=100-S.pricing.deposit;
  document.getElementById('tg-vat').classList.toggle('on',S.pricing.vat);
  document.getElementById('tg-wht').classList.toggle('on',S.pricing.wht);
}
function exportJSON(){saveMeta();const name=(S.meta.project||'budget').replace(/[^a-z0-9]+/gi,'-').toLowerCase();
  const blob=new Blob([JSON.stringify(S,null,2)],{type:'application/json'});const a=document.createElement('a');
  a.href=URL.createObjectURL(blob);a.download=`jmos-${name}.json`;a.click();URL.revokeObjectURL(a.href);toast('Budget saved to file');}
function importJSON(e){const f=e.target.files[0];if(!f)return;const rd=new FileReader();
  rd.onload=()=>{try{const o=JSON.parse(rd.result);if(!o.data||!o.pricing)throw 0;
    o.contact=o.contact||seed().contact;o.quote=o.quote||seed().quote;S=o;syncInputs();setMarkup(S.pricing.markup);renderAll();toast('Budget loaded');}
    catch(err){toast('That file could not be read as a JMOS budget');}};
  rd.readAsText(f);e.target.value='';}

/* ---- QUOTE ---- */
function defaultQuoteItems(){
  const markupMultiplier = 1 + ((S.pricing.markup || 0) / 100);
  const items = [];
  const secKeys = Object.keys(SECTIONS);

  // Group items by each section defined in the budget entries
  secKeys.forEach(secKey => {
    const secCfg = SECTIONS[secKey];
    const rawRows = S.data[secKey] || [];
    
    // Filter active items (rows with a non-empty name or rate > 0)
    const activeRows = rawRows.filter(r => r && (r.name && r.name.trim() !== ''));
    const secCost = rawRows.reduce((sum, r) => sum + (num(r.rate) * num(r.qty)), 0);

    if (activeRows.length > 0 && secCost > 0) {
      const deliverables = activeRows.map(r => {
        const name = r.name.trim();
        const qty = num(r.qty);
        const qtyUnit = secCfg.cols[2] || 'Units';
        const qtyStr = qty > 1 ? ` (${qty} ${qtyUnit.toLowerCase()})` : '';
        return `${name}${qtyStr}`;
      });

      // Marked up section price (final client figure with markup applied, backend markup hidden)
      const secClientAmount = Math.round(secCost * markupMultiplier);

      items.push({
        name: secCfg.title.toUpperCase(),
        deliverables: deliverables,
        qty: 1,
        amount: secClientAmount
      });
    }
  });

  // If no sections had positive costs, fallback to project title / generic item with total fee
  if (items.length === 0) {
    const c = calc();
    return [{
      name: S.meta.project ? S.meta.project.replace(/—.*$/, '').trim().toUpperCase() || 'PRODUCTION SERVICES' : 'PRODUCTION SERVICES',
      deliverables: ['Full creative production & filming', 'Post-production & master delivery', 'Social media revisions & deliverables'],
      qty: 1,
      amount: Math.round(c.fee)
    }];
  }

  // Reconcile any rounding difference so the sum of section amounts matches calc().fee exactly
  const totalFee = Math.round(calc().fee);
  const currentSum = items.reduce((sum, it) => sum + num(it.amount), 0);
  const diff = totalFee - currentSum;
  if (diff !== 0 && items.length > 0) {
    items[0].amount += diff;
  }

  return items;
}

function quoteSubtotal(){return (S.quote.items||[]).reduce((s,it)=>s+num(it.amount),0);}
function quoteTotals(){
  const sub=quoteSubtotal(),vat=S.pricing.vat?sub*0.16:0,gross=sub+vat,wht=S.pricing.wht?sub*0.05:0,net=gross-wht;
  const dep=net*(S.pricing.deposit/100),bal=net-dep;return {sub,vat,gross,wht,net,dep,bal};
}
function resetQuoteFromBudget(){S.quote.items=defaultQuoteItems();renderQuote();toast('Quote reset to section breakdowns with markup');}

function openQuote(){
  saveMeta();
  if(!S.quote.items || S.quote._autoSync !== false) {
    S.quote.items = defaultQuoteItems();
  }
  renderQuote();
  document.getElementById('quote-overlay').classList.add('show');
  document.body.style.overflow='hidden';
}
function closeQuote(){document.getElementById('quote-overlay').classList.remove('show');document.body.style.overflow='';}

function renderQuote(){
  const q=S.quote,ct=S.contact,t=quoteTotals();
  const validUntil=plusDays(S.meta.date,30);
  let rows='';
  (q.items||[]).forEach((it,i)=>{
    const bullets=(it.deliverables||[]).map(d=>`<li>${esc(d)}</li>`).join('')||'<li>Add deliverables…</li>';
    rows+=`<tr>
      <td class="it-name"><span class="qi" contenteditable onblur="qItemName(${i},this)">${esc(it.name)}</span></td>
      <td><ul class="q-deliv" contenteditable onblur="qDeliv(${i},this)">${bullets}</ul></td>
      <td class="q-qty"><span class="qi" contenteditable onblur="qQty(${i},this)">${it.qty}</span></td>
      <td class="q-amt-cell"><span class="qi" contenteditable onfocus="qAmtRaw(this,${i})" onblur="qAmt(${i},this)">${fmt(it.amount)}</span></td>
      <td class="q-del"><button title="Remove item" onclick="qDel(${i})"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 6L6 18M6 6l12 12"/></svg></button></td>
    </tr>`;
  });
  const vatRow=S.pricing.vat?`<div class="row"><span class="k">VAT (16%)</span><span class="v">${fmt(t.vat)}</span></div>
    <div class="row"><span class="k">Total incl. VAT</span><span class="v">${fmt(t.gross)}</span></div>`:'';
  const whtRow=S.pricing.wht?`<div class="row wht"><span class="k">Less: withholding tax (5%)</span><span class="v">− ${fmt(t.wht)}</span></div>`:'';
  const grandLbl=S.pricing.wht?'Net payable':'Total payable';
  const whtCallout=S.pricing.wht?`<div class="wht-callout"><b>Withholding tax.</b> As an eTIMS-registered client, you deduct 5% (${fmt(t.wht)}) and remit it to KRA on our behalf. Kindly share the withholding-tax certificate — the amount is credited against our tax and does not reduce the agreed fee.</div>`:'';
  const notesHtml=(q.notes||[]).map((n,i)=>`<li><span class="qedit" contenteditable onblur="qNote(${i},this)">${esc(n)}</span></li>`).join('');

  document.getElementById('quote-doc').innerHTML=`
  <div class="q-banner">
    <div>
      <div class="co">JEOTA MEDIA LTD</div>
      <div class="tag">Storytellers for a better world</div>
    </div>
    <div class="right">
      <div class="contact">
        <div><span class="qedit" contenteditable onblur="qCt('email',this)">${esc(ct.email)}</span></div>
        <div><span class="qedit" contenteditable onblur="qCt('phone',this)">${esc(ct.phone)}</span></div>
        <div><span class="qedit" contenteditable onblur="qCt('web',this)">${esc(ct.web)}</span></div>
        <div><span class="qedit" contenteditable onblur="qCt('addr1',this)">${esc(ct.addr1)}</span></div>
        <div><span class="qedit" contenteditable onblur="qCt('addr2',this)">${esc(ct.addr2)}</span></div>
      </div>
      <div class="q-logo"><img src="${LOGO}" alt="Jeota Media"></div>
    </div>
  </div>

  <div class="q-body">
    <div class="q-top">
      <div>
        <div class="big">QUOTE</div>
        <div class="meta-l">
          <div>Date: <span class="qedit" contenteditable onblur="S.meta.date=parseDateEdit(this.textContent)||S.meta.date">${prettyDate(S.meta.date)}</span></div>
          <div>Quote no. <span class="qedit" contenteditable onblur="S.meta.quoteNo=this.textContent.trim()">${esc(S.meta.quoteNo)}</span></div>
          <div>Valid until: <span class="qedit" contenteditable>${prettyDate(validUntil)}</span></div>
        </div>
      </div>
      <div class="meta-r">
        <div class="k">Project</div><span class="v qedit" contenteditable onblur="S.meta.project=this.textContent.trim()">${esc(S.meta.project||'—')}</span>
        <div class="k">Prepared for</div><span class="v qedit" contenteditable onblur="S.meta.client=this.textContent.trim()">${esc(S.meta.client||'—')}</span>
      </div>
    </div>

    <table class="q-table">
      <thead><tr><th>Item</th><th>Description</th><th class="n" style="text-align:center">Qty</th><th class="n">${CUR[S.meta.currency].sym}</th><th></th></tr></thead>
      <tbody>${rows}</tbody>
      <tfoot><tr class="q-total-row"><td colspan="3" class="lbl">Total</td><td class="val">${fmt(t.sub)}</td><td></td></tr></tfoot>
    </table>
    <button class="q-additem" onclick="qAdd()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>Add item</button>

    <div class="q-totals">
      <div class="row"><span class="k">Subtotal</span><span class="v">${fmt(t.sub)}</span></div>
      ${vatRow}${whtRow}
      <div class="row grand"><span class="k">${grandLbl}</span><span class="v">${fmt(t.net)}</span></div>
    </div>
    ${whtCallout}

    <div class="q-sections">
      <div>
        <h5>Payment terms</h5>
        <div class="terms-pay">
          <div class="cell"><div class="k">${S.pricing.deposit}% deposit</div><div class="v">${fmt(t.dep)}</div></div>
          <div class="cell"><div class="k">${100-S.pricing.deposit}% on delivery</div><div class="v">${fmt(t.bal)}</div></div>
        </div>
        <p style="margin-top:8px">The deposit confirms the booking and starts production. The balance is due on final delivery.</p>
      </div>
      <div>
        <h5>Payment methods</h5>
        <div class="pay-line"><span class="pk">M-Pesa</span><span class="pv qedit" contenteditable onblur="qPay('mpesa',this)">${esc(q.mpesa)}</span></div>
        <div class="pay-line"><span class="pk">Bank</span><span class="pv qedit" contenteditable onblur="qPay('bank',this)">${esc(q.bank)}</span></div>
        <div class="pay-line"><span class="pk">Cheque</span><span class="pv qedit" contenteditable onblur="qPay('cheque',this)">${esc(q.cheque)}</span></div>
      </div>
    </div>

    <div class="q-notes">
      <h5>Notes</h5>
      <ul>${notesHtml}</ul>
    </div>

    <div class="q-foot">Thank you for choosing Jeota Media Ltd · ${esc(ct.web)}</div>
  </div>`;
}

/* quote edit handlers */
function qItemName(i,el){S.quote.items[i].name=el.textContent.trim();S.quote._autoSync=false;}
function qDeliv(i,el){S.quote.items[i].deliverables=[...el.querySelectorAll('li')].map(li=>li.textContent.trim()).filter(Boolean);S.quote._autoSync=false;}
function qQty(i,el){S.quote.items[i].qty=num(el.textContent)||1;el.textContent=S.quote.items[i].qty;S.quote._autoSync=false;}
function qAmtRaw(el,i){el.textContent=String(num(S.quote.items[i].amount)||'');}
function qAmt(i,el){S.quote.items[i].amount=num(el.textContent);S.quote._autoSync=false;renderQuote();}
function qDel(i){if(S.quote.items.length<=1){toast('Keep at least one item');return;}S.quote.items.splice(i,1);S.quote._autoSync=false;renderQuote();}
function qAdd(){S.quote.items.push({name:'ITEM',deliverables:['Deliverable'],qty:1,amount:0});S.quote._autoSync=false;renderQuote();}
function qCt(k,el){S.contact[k]=el.textContent.trim();}
function qPay(k,el){S.quote[k]=el.textContent.trim();}
function qNote(i,el){S.quote.notes[i]=el.textContent.trim();}

function sendQuoteEmail() {
  const t = calc();
  const clientName = S.meta.client || 'Client';
  const projectName = S.meta.project || 'Production Scope';
  const quoteNo = S.meta.quoteNo || 'JM-Q-0001';
  const totalStr = fmt(t.net);
  const depStr = fmt(t.dep);
  const balStr = fmt(t.bal);

  let itemsText = '';
  (S.quote.items || defaultQuoteItems()).forEach(it => {
    itemsText += `• ${it.name}: ${fmt(it.amount)}\n`;
    if (it.deliverables && it.deliverables.length) {
      itemsText += `  Deliverables: ${it.deliverables.join(', ')}\n`;
    }
  });

  const body = `Dear ${clientName},\n\n` +
    `Please find the commercial quotation for ${projectName} (Ref: ${quoteNo}) detailed below:\n\n` +
    `----------------------------------------\n` +
    `SCOPE & DELIVERABLES:\n` +
    `${itemsText}\n` +
    `TOTAL PAYABLE: ${totalStr}\n` +
    `PAYMENT TERMS:\n` +
    `• ${S.pricing.deposit}% Deposit: ${depStr}\n` +
    `• ${100 - S.pricing.deposit}% On Final Delivery: ${balStr}\n\n` +
    `PAYMENT METHODS:\n` +
    `• M-Pesa: ${S.quote.mpesa || 'Paybill 880100 · Acc: 352655'}\n` +
    `• Bank: ${S.quote.bank || 'NCBA — Junction Branch · A/C: Jeota Media Limited'}\n\n` +
    `Thank you for choosing Jeota Media.\n\n` +
    `Best regards,\n` +
    `Jeota Media Ltd\n` +
    `${S.contact.phone || '+254 791 388 683'} · ${S.contact.email || 'info@jeotamedia.co.ke'}\n` +
    `https://${S.contact.web || 'www.jeotamedia.co.ke'}`;

  const defaultEmail = (S.contact && S.contact.clientEmail) || '';
  const recipient = prompt(`Enter recipient email address:`, defaultEmail);
  if (recipient === null) return;

  const mailtoUrl = `mailto:${encodeURIComponent(recipient.trim())}?subject=${encodeURIComponent(`Quotation: ${projectName} (${quoteNo}) — Jeota Media`)}&body=${encodeURIComponent(body)}`;
  window.location.href = mailtoUrl;
  toast('Opening email client…');
}

function esc(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
let toastT;function toast(m){const t=document.getElementById('toast');t.textContent=m;t.classList.add('show');clearTimeout(toastT);toastT=setTimeout(()=>t.classList.remove('show'),2200);}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeQuote();});

syncInputs();setMarkup(40);renderAll();
</script>
</body>
</html>
