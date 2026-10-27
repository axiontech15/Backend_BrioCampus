<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="color-scheme" content="light">
  <title>Brio Campus — Votre diplôme, vérifié à la source</title>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap">
  <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.min.css') }}">
  <style>
    :root {
      --bc-blue: #2563EB;
      --bc-blue-hover: #1D4ED8;
      --bc-blue-dark: #1A2642;
      --bc-blue-deeper: #111827;
      --bc-blue-soft: #EFF6FF;
      --bc-border: #BFDBFE;
      --bc-surface: #FFFFFF;
      --bc-bg: #FFFFFF;
      --bc-bg-alt: #F0F4FF;
      --bc-ink: #1A2642;
      --bc-slate: #4A5568;
      --bc-slate-muted: #64748B;
      --bc-label: #4F709C;
      --bc-gradient-night: linear-gradient(135deg, #1A2642 0%, #111827 100%);
      --bc-focus-ring: 0 0 0 3px rgba(37, 99, 235, .35);
      --shadow-card: 0 10px 30px rgba(26, 38, 66, .12);
      color-scheme: light;
      box-sizing: border-box;
      padding-top: env(safe-area-inset-top, 0px);
      padding-bottom: env(safe-area-inset-bottom, 0px)
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box
    }

    html {
      scroll-behavior: smooth;
      scroll-padding-top: 70px
    }

    body {
      margin: 0;
      background: var(--bc-bg);
      color: var(--bc-slate);
      font-family: Manrope, system-ui, sans-serif;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased
    }

    a {
      color: inherit;
      text-decoration: none
    }

    :focus-visible {
      outline: none;
      box-shadow: var(--bc-focus-ring)
    }

    .mono {
      font-family: "DM Mono", ui-monospace, monospace;
      font-size: 11px;
      letter-spacing: .06em
    }

    .wrap {
      max-width: 1120px;
      margin: 0 auto;
      padding: 0 20px
    }

    h1,
    h2,
    h3 {
      margin: 0;
      font-weight: 500;
      letter-spacing: -.03em;
      line-height: 1.1;
      color: var(--bc-ink)
    }

    /* Hero */
    .hero {
      margin: 10px;
      border-radius: 22px;
      position: relative;
      overflow: hidden;
      min-height: 650px;
      color: #fff;
      padding: 22px 28px 30px;
      background: radial-gradient(50% 38% at 12% 78%, rgba(37, 99, 235, .55), transparent 70%), radial-gradient(40% 30% at 90% 60%, rgba(96, 165, 250, .35), transparent 70%), var(--bc-gradient-night)
    }

    nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
      font-size: 17px;
      color: #fff
    }

    .logo img {
      display: block;
      height: 34px;
      width: auto
    }

    .logo .logo-mini {
      display: none;
      height: 36px
    }

    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0
    }

    .links {
      display: flex;
      gap: 30px
    }

    .links a {
      color: #fff;
      opacity: .85;
      text-transform: uppercase
    }

    .links a:hover {
      opacity: 1;
      text-decoration: underline
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      border: 0;
      border-radius: 99px;
      padding: 9px 16px;
      font: 500 11px "DM Mono", monospace;
      letter-spacing: .06em;
      text-transform: uppercase;
      cursor: pointer;
      background: var(--bc-blue);
      color: #fff;
      transition: background .2s
    }

    .btn:hover {
      background: var(--bc-blue-hover)
    }

    .btn .ar {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: #fff;
      color: var(--bc-blue);
      display: grid;
      place-items: center;
      font-size: 12px
    }

    .btn.ghost {
      background: rgba(255, 255, 255, .12);
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .3)
    }

    .btn.ghost:hover {
      background: rgba(255, 255, 255, .2)
    }

    .btn.dark {
      background: var(--bc-blue-dark)
    }

    .hero-in {
      text-align: center;
      max-width: 640px;
      margin: 76px auto 0
    }

    .hero h1 {
      font-size: clamp(34px, 5.4vw, 54px);
      color: #fff
    }

    .hero p {
      margin: 18px auto 22px;
      font-size: 13px;
      max-width: 500px;
      color: #dbe4f3
    }

    .cta {
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap
    }

    .deck {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 14px;
      margin: 48px auto 0;
      perspective: 1200px;
      max-width: 960px
    }

    .tile {
      flex: 0 0 auto;
      border-radius: 14px;
      padding: 12px;
      background: var(--bc-surface);
      color: var(--bc-ink);
      box-shadow: 0 14px 34px rgba(0, 0, 0, .35);
      font-size: 11px;
      transition: transform .35s
    }

    .tile:hover {
      transform: translateY(-6px)
    }

    .t1 {
      width: 130px;
      transform: rotateY(18deg)
    }

    .t2 {
      width: 150px;
      transform: rotateY(10deg)
    }

    .t4 {
      width: 150px;
      transform: rotateY(-10deg)
    }

    .t5 {
      width: 140px;
      transform: rotateY(-18deg)
    }

    .t3 {
      width: 170px;
      height: 150px;
      background: rgba(37, 99, 235, .55);
      backdrop-filter: blur(8px);
      color: #fff;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      font-size: 14px;
      font-weight: 600;
      border: 1px solid rgba(255, 255, 255, .35)
    }

    .t3 b {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background: #fff;
      color: var(--bc-blue);
      display: grid;
      place-items: center;
      font-size: 18px
    }

    .t3 small {
      font-weight: 400;
      font-size: 10px;
      opacity: .9
    }

    .tile .big {
      font-size: 26px;
      font-weight: 500;
      letter-spacing: -.03em
    }

    .tile .top {
      background: var(--bc-blue-dark);
      color: #fff;
      border-radius: 8px;
      padding: 7px 9px;
      margin-bottom: 8px;
      display: flex;
      justify-content: space-between
    }

    .chips {
      display: flex;
      gap: 4px;
      flex-wrap: wrap;
      margin-top: 6px
    }

    .chips span {
      border: 1px solid var(--bc-border);
      color: var(--bc-label);
      border-radius: 6px;
      padding: 2px 6px;
      font-size: 9.5px
    }

    .t4 {
      background: var(--bc-blue-dark);
      color: #fff;
      line-height: 1.35
    }

    .t4 em {
      font-style: normal;
      color: #93C5FD
    }

    .bars {
      display: flex;
      align-items: flex-end;
      gap: 4px;
      height: 60px;
      margin-top: 8px
    }

    .bars i {
      flex: 1;
      background: var(--bc-blue);
      border-radius: 3px 3px 0 0
    }

    .rate {
      text-align: center;
      margin-top: 22px;
      font-size: 11px;
      color: #c3cee3
    }

    /* Marquee */
    .marq {
      overflow: hidden;
      margin: 26px 0;
      mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent)
    }

    .track {
      display: flex;
      gap: 56px;
      width: max-content;
      animation: slide 32s linear infinite
    }

    .track span {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #6b7280;
      font-weight: 600;
      font-size: 14px;
      white-space: nowrap
    }

    .track span::before {
      content: "";
      width: 16px;
      height: 16px;
      border-radius: 50%;
      border: 3px dotted #9ca3af
    }

    @keyframes slide {
      to {
        transform: translateX(-50%)
      }
    }

    /* Sections */
    section {
      padding: 64px 0
    }

    .alt {
      background: var(--bc-bg-alt)
    }

    .eyebrow {
      display: block;
      text-align: center;
      margin-bottom: 18px;
      color: var(--bc-label)
    }

    .sec-h {
      font-size: clamp(28px, 4.2vw, 40px);
      text-align: center;
      max-width: 700px;
      margin: 0 auto
    }

    .sec-h .soft {
      color: var(--bc-slate-muted)
    }

    .dot {
      display: inline-grid;
      place-items: center;
      width: .95em;
      height: .95em;
      border-radius: 50%;
      vertical-align: -.1em;
      font-size: .8em;
      background: var(--bc-blue);
      color: #fff
    }

    .sec-p {
      text-align: center;
      max-width: 520px;
      margin: 18px auto 22px;
      font-size: 13px;
      color: var(--bc-slate-muted)
    }

    .bento {
      display: grid;
      grid-template-columns: 1.1fr 1fr 1fr;
      gap: 14px;
      margin-top: 46px
    }

    .b {
      border-radius: 16px;
      padding: 18px;
      min-height: 150px
    }

    .b.photo {
      grid-row: span 2;
      min-height: 310px;
      color: #fff;
      background: radial-gradient(70% 50% at 20% 85%, rgba(147, 197, 253, .55), transparent 70%), linear-gradient(170deg, var(--bc-blue), var(--bc-blue-dark));
      display: flex;
      flex-direction: column;
      justify-content: space-between
    }

    .b.photo .tag {
      font-weight: 700;
      font-size: 18px;
      letter-spacing: .02em
    }

    .b.photo .box {
      background: #fff;
      color: var(--bc-slate-muted);
      border-radius: 12px;
      padding: 14px;
      font-size: 12px
    }

    .b.photo .box strong {
      display: block;
      font-size: 34px;
      font-weight: 500;
      letter-spacing: -.03em;
      line-height: 1;
      color: var(--bc-ink)
    }

    .b.grey {
      background: var(--bc-blue-soft);
      box-shadow: inset 0 0 0 1px var(--bc-border);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 14px
    }

    .b.grey .n {
      font-size: 36px;
      font-weight: 500;
      letter-spacing: -.03em;
      color: var(--bc-ink)
    }

    .b.grey q {
      font-size: 13px;
      color: var(--bc-slate);
      quotes: none
    }

    .b.accent {
      background: var(--bc-blue);
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: space-between
    }

    .b.accent .n {
      font-size: 30px;
      font-weight: 500;
      letter-spacing: -.03em;
      line-height: 1.1
    }

    .b.black {
      background: var(--bc-gradient-night);
      color: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center
    }

    .b.black strong {
      font-size: 30px;
      font-weight: 500
    }

    .lbl {
      font-size: 12px;
      opacity: .8
    }

    /* Publics */
    .cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-top: 40px;
      padding: 10px;
      border-radius: 20px;
      background: var(--bc-surface);
      box-shadow: var(--shadow-card), 0 0 0 1px var(--bc-border)
    }

    .c {
      background: var(--bc-blue-soft);
      border-radius: 14px;
      padding: 18px;
      min-height: 240px;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      gap: 6px
    }

    .c .ic {
      width: 26px;
      height: 26px;
      border-radius: 7px;
      background: var(--bc-blue);
      color: #fff;
      display: grid;
      place-items: center;
      font-weight: 700;
      font-size: 13px;
      margin-bottom: auto
    }

    .c h3 {
      font-size: 16px;
      font-weight: 600;
      letter-spacing: -.01em
    }

    .c p {
      margin: 0;
      font-size: 12.5px;
      color: var(--bc-slate-muted)
    }

    .c .slogan {
      color: var(--bc-blue);
      font-weight: 600;
      font-size: 13px;
      margin-top: 4px
    }

    .c.img {
      padding: 0;
      overflow: hidden;
      background: linear-gradient(160deg, var(--bc-blue-dark) 0%, var(--bc-blue) 100%);
      justify-content: flex-end
    }

    .c.img div {
      padding: 18px
    }

    .c.img h3,
    .c.img p {
      color: #fff
    }

    /* Parcours */
    .steps {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 8px;
      margin-top: 40px
    }

    .step {
      border: 1px solid var(--bc-border);
      background: var(--bc-surface);
      color: var(--bc-ink);
      border-radius: 14px;
      padding: 14px;
      text-align: left;
      cursor: pointer;
      font: inherit;
      transition: background .2s, border-color .2s
    }

    .step .k {
      display: block;
      color: var(--bc-label);
      margin-bottom: 10px
    }

    .step b {
      font-weight: 600;
      font-size: 14px;
      letter-spacing: -.01em
    }

    .step[aria-selected="true"] {
      background: var(--bc-blue);
      border-color: var(--bc-blue);
      color: #fff
    }

    .step[aria-selected="true"] .k {
      color: #dbeafe
    }

    .bar {
      height: 6px;
      border-radius: 9px;
      background: var(--bc-border);
      margin-top: 18px;
      overflow: hidden
    }

    .bar i {
      display: block;
      height: 100%;
      width: 20%;
      background: var(--bc-blue);
      border-radius: 9px;
      transition: width .5s
    }

    .detail {
      margin-top: 18px;
      border-radius: 16px;
      background: var(--bc-gradient-night);
      color: #fff;
      padding: 24px;
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 24px
    }

    .detail h3 {
      font-size: 24px;
      margin-bottom: 10px;
      color: #fff
    }

    .detail p {
      margin: 0;
      color: #cbd5e1;
      font-size: 14px;
      max-width: 46ch
    }

    .proof {
      background: rgba(255, 255, 255, .08);
      border-radius: 12px;
      padding: 14px;
      font-size: 12.5px;
      color: #e5e8ec
    }

    .proof .mono {
      display: block;
      color: #93C5FD;
      margin-bottom: 6px
    }

    .final {
      text-align: center;
      padding: 64px 0 80px
    }

    footer {
      position: relative;
      background: radial-gradient(36% 44% at 82% 8%, rgba(37, 99, 235, .32), transparent 72%), var(--bc-gradient-night);
      color: #cbd5e1;
      padding: 56px 0 24px;
      font-size: 13px
    }

    .foot-grid {
      display: grid;
      grid-template-columns: 1.5fr .8fr .8fr 1.15fr;
      gap: 48px;
      align-items: start
    }

    .foot-brand img {
      display: block;
      width: 210px;
      max-width: 100%;
      height: auto;
      margin-bottom: 16px
    }

    .foot-brand p,
    .foot-note {
      margin: 0;
      max-width: 290px;
      color: #cbd5e1
    }

    .foot-social {
      display: flex;
      gap: 10px;
      margin-top: 18px
    }

    .foot-social a {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: rgba(255, 255, 255, .08);
      color: #fff;
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .12);
      transition: background .2s, transform .2s
    }

    .foot-social a:hover,
    .to-top:hover {
      background: var(--bc-blue);
      transform: translateY(-2px)
    }

    .foot-col h3,
    .newsletter h3 {
      margin: 0 0 14px;
      color: #fff;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0
    }

    .foot-col a {
      display: block;
      width: max-content;
      max-width: 100%;
      color: #cbd5e1;
      margin: 10px 0;
      transition: color .2s
    }

    .foot-col a:hover {
      color: #fff
    }

    .newsletter-form {
      display: flex;
      gap: 8px;
      margin-top: 14px
    }

    .newsletter-form input {
      min-width: 0;
      flex: 1;
      border: 0;
      border-radius: 999px;
      padding: 13px 16px;
      font: inherit;
      color: var(--bc-ink);
      background: #fff
    }

    .newsletter-form button {
      border: 0;
      border-radius: 999px;
      padding: 0 18px;
      font: 700 12px Manrope, system-ui, sans-serif;
      color: #fff;
      background: var(--bc-blue);
      cursor: pointer;
      box-shadow: 0 12px 28px rgba(37, 99, 235, .28)
    }

    .newsletter-form button:hover {
      background: var(--bc-blue-hover)
    }

    .foot-bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 18px;
      flex-wrap: wrap;
      margin-top: 48px;
      padding-top: 22px;
      border-top: 1px solid rgba(255, 255, 255, .12);
      color: #94a3b8;
      font-size: 12px
    }

    .to-top {
      position: absolute;
      right: 22px;
      bottom: 22px;
      width: 42px;
      height: 42px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: rgba(37, 99, 235, .9);
      color: #fff;
      box-shadow: 0 16px 34px rgba(37, 99, 235, .28);
      transition: background .2s, transform .2s
    }

    @media (max-width:860px) {
      .links {
        display: none
      }

      .hero {
        padding: 18px 16px 26px;
        min-height: 0
      }

      .logo .logo-full {
        display: none
      }

      .logo .logo-mini {
        display: block
      }

      .hero-in {
        margin-top: 48px
      }

      .deck {
        overflow-x: auto;
        justify-content: flex-start;
        padding: 10px 6px 20px;
        perspective: none
      }

      .tile {
        transform: none !important
      }

      .bento,
      .cards,
      .detail {
        grid-template-columns: 1fr
      }

      .b.photo {
        grid-row: auto;
        min-height: 260px
      }

      .b.black {
        grid-column: auto !important
      }

      .steps {
        grid-template-columns: 1fr 1fr
      }

      .foot-grid {
        grid-template-columns: 1fr 1fr;
        gap: 30px
      }

      .foot-brand,
      .newsletter {
        grid-column: 1 / -1
      }
    }

    @media (max-width:560px) {
      footer {
        padding-top: 44px
      }

      .foot-grid {
        grid-template-columns: 1fr
      }

      .newsletter-form {
        flex-direction: column
      }

      .newsletter-form button {
        min-height: 44px
      }

      .to-top {
        position: static;
        margin: 22px 0 0 auto
      }
    }

    @media (prefers-reduced-motion:reduce) {
      .track {
        animation: none
      }

      html {
        scroll-behavior: auto
      }

      .tile,
      .bar i,
      .btn {
        transition: none
      }
    }
  </style>
</head>

<body>

@include('layouts.header')
  <div class="marq" aria-hidden="true">
    <div class="track" id="track">
      <span>Recruteurs</span><span>Écoles</span><span>Ambassades</span><span>Diplômés</span><span>Universités</span><span>Ministère</span>
    </div>
  </div>

  <section id="apropos">
    <div class="wrap">
      <span class="eyebrow mono">• À propos</span>
      <h2 class="sec-h">Une application web pour des diplômes <span class="dot">✓</span> vérifiés <span class="soft">et
          un parcours</span> <span class="dot">•</span> <span class="soft">réutilisable</span></h2>
      <p class="sec-p">Elle permet aux recruteurs de faire vérifier, à la source, les diplômes délivrés en RDC, et aux
        diplômés de constituer un dossier de parcours réutilisable.</p>
      <div class="bento">
        <div class="b photo">
          <span class="tag">BRIO</span>
          <div class="box"><strong>5 étapes</strong>Du dépôt à la réutilisation, avec une preuve à chaque étape.</div>
        </div>
        <div class="b grey">
          <div><span class="lbl">Engagement de traçabilité</span>
            <div class="n">100 %</div>
          </div>
          <q>Institutionnel, mais pas administratif.</q>
        </div>
        <div class="b accent">
          <span class="lbl">Une seule question par écran</span>
          <div class="n">Où en est ce dossier, et sur quelle preuve ?</div>
        </div>
        <div class="b black" style="grid-column:span 2">
          <span class="lbl">Publics servis</span><strong>2</strong>
        </div>
      </div>
    </div>
  </section>

  <section id="publics" class="alt">
    <div class="wrap">
      <span class="eyebrow mono">• Deux publics</span>
      <h2 class="sec-h">Une même vérification, deux besoins</h2>
      <p class="sec-p">Recruteurs et diplômés partagent un dossier unique, daté et contrôlable par tous.</p>
      <div style="text-align:center"><a class="btn dark" href="#parcours">Voir le parcours <span class="ar">↗</span></a>
      </div>
      <div class="cards">
        <article class="c">
          <div class="ic">R</div>
          <h3>Recruteurs, écoles, ambassades</h3>
          <p>Besoin de certitude, de traçabilité et de délais tenus.</p>
          <p class="slogan">Recrutez sur des preuves, pas sur des copies.</p>
        </article>
        <article class="c">
          <div class="ic">D</div>
          <h3>Diplômés</h3>
          <p>Besoin d’être crus, sans démarches répétées auprès de chaque interlocuteur.</p>
          <p class="slogan">La preuve de votre parcours.</p>
        </article>
        <article class="c img">
          <div>
            <h3>Dossier de parcours</h3>
            <p>Un rapport réutilisable, que le diplômé partage à d’autres recruteurs.</p>
          </div>
        </article>
      </div>
    </div>
  </section>

  <section id="parcours">
    <div class="wrap">
      <span class="eyebrow mono">• Parcours de référence</span>
      <h2 class="sec-h">Du dépôt à la réutilisation, étape par étape</h2>
      <div class="steps" role="group" aria-label="Étapes du parcours" id="steps"></div>
      <div class="bar" aria-hidden="true"><i id="fill"></i></div>
      <div class="detail" aria-live="polite">
        <div>
          <h3 id="d-title"></h3>
          <p id="d-text"></p>
        </div>
        <div class="proof"><span class="mono" id="d-k"></span><span id="d-proof"></span></div>
      </div>
    </div>
  </section>

  <section class="final alt" id="contact">
    <div class="wrap">
      <h2 class="sec-h">La preuve de votre parcours.</h2>
      <p class="sec-p">Déposez les pièces : Brio Campus se charge du contrôle et de la confirmation auprès de
        l’établissement.</p>
      <a class="btn" href="#accueil">Ouvrir un dossier <span class="ar">↗</span></a>
    </div>
  </section>
@include('layouts.footer')
  <script>
    (function () {
      var data = [
        { t: "Dépôt", d: "Le diplômé ou le recruteur ouvre un dossier et joint les pièces.", k: "Preuve attendue", p: "Pièces jointes au dossier : diplôme, relevés et pièce d’identité." },
        { t: "Contrôle", d: "Recevabilité des pièces et identification de l’établissement.", k: "Preuve produite", p: "Pièces déclarées recevables et établissement identifié." },
        { t: "Vérification à la source", d: "Confirmation auprès de l’université ou du ministère.", k: "Preuve produite", p: "Réponse écrite de l’université ou du ministère." },
        { t: "Attestation", d: "Rapport daté, code du dossier et lien de contrôle public.", k: "Preuve produite", p: "Rapport daté, code du dossier et lien de contrôle public." },
        { t: "Réutilisation", d: "Le diplômé partage son rapport à d’autres recruteurs.", k: "Preuve partagée", p: "Le même rapport, consultable par chaque nouveau recruteur." }
      ];
      var box = document.getElementById('steps'), fill = document.getElementById('fill');
      data.forEach(function (s, i) {
        var b = document.createElement('button');
        b.className = 'step'; b.type = 'button'; b.setAttribute('aria-selected', 'false');
        b.innerHTML = '<span class="k mono">Étape ' + (i + 1) + '</span><b>' + s.t + '</b>';
        b.addEventListener('click', function () { show(i) });
        box.appendChild(b);
      });
      function show(i) {
        var s = data[i];
        [].forEach.call(box.children, function (el, j) { el.setAttribute('aria-selected', j === i ? 'true' : 'false') });
        document.getElementById('d-title').textContent = s.t;
        document.getElementById('d-text').textContent = s.d;
        document.getElementById('d-k').textContent = s.k;
        document.getElementById('d-proof').textContent = s.p;
        fill.style.width = ((i + 1) / data.length * 100) + '%';
      }
      show(0);
      var tr = document.getElementById('track'); tr.innerHTML += tr.innerHTML;

    })();
  </script>
</body>

</html>