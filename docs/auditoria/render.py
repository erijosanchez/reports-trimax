#!/usr/bin/env python3
"""Renderiza docs/auditoria/*.md a HTML estatico y navegable."""
import re
import pathlib
import markdown

BASE = pathlib.Path("/home/pm-trimax/COMERCIAL/reports-trimax/docs/auditoria")
OUT = BASE / "html"
OUT.mkdir(exist_ok=True)

PAGES = [
    ("README.md", "index.html", "Resumen"),
    ("CORRECCIONES.md", "correcciones.html", "Correcciones"),
    ("SEGURIDAD.md", "seguridad.html", "Seguridad"),
    ("ARQUITECTURA.md", "arquitectura.html", "Arquitectura"),
    ("INFRAESTRUCTURA.md", "infraestructura.html", "Infraestructura"),
    ("FRONTEND.md", "frontend.html", "Frontend"),
]

CSS = """
*,*::before,*::after{box-sizing:border-box}
:root{
  --bg:#fbfbfa; --surface:#fff; --text:#1c1c1a; --muted:#6b6b66;
  --border:#e4e3df; --accent:#2f6f4f; --accent-soft:#eaf3ee;
  --code-bg:#f5f5f3; --shadow:0 1px 2px rgba(0,0,0,.05),0 8px 24px rgba(0,0,0,.04);
  --critica:#8c1d18; --alta:#b3261e; --alta-bg:#fdecea; --media:#8a5a00; --media-bg:#fdf3e0;
  --baja:#3f6212; --baja-bg:#f0f6e6;
}
@media (prefers-color-scheme:dark){
  :root{
    --bg:#16171a; --surface:#1e1f23; --text:#e6e5e2; --muted:#9a9a95;
    --border:#2e2f34; --accent:#6fbf90; --accent-soft:#1d2b23;
    --code-bg:#232429; --shadow:0 1px 2px rgba(0,0,0,.4),0 8px 24px rgba(0,0,0,.25);
    --critica:#c5372c; --alta:#ff8a80; --alta-bg:#331e1c; --media:#f0c169; --media-bg:#332913;
    --baja:#a3d977; --baja-bg:#20291a;
  }
}
:root[data-theme="dark"]{
  --bg:#16171a; --surface:#1e1f23; --text:#e6e5e2; --muted:#9a9a95;
  --border:#2e2f34; --accent:#6fbf90; --accent-soft:#1d2b23;
  --code-bg:#232429; --shadow:0 1px 2px rgba(0,0,0,.4),0 8px 24px rgba(0,0,0,.25);
  --critica:#c5372c; --alta:#ff8a80; --alta-bg:#331e1c; --media:#f0c169; --media-bg:#332913;
  --baja:#a3d977; --baja-bg:#20291a;
}
:root[data-theme="light"]{
  --bg:#fbfbfa; --surface:#fff; --text:#1c1c1a; --muted:#6b6b66;
  --border:#e4e3df; --accent:#2f6f4f; --accent-soft:#eaf3ee;
  --code-bg:#f5f5f3; --critica:#8c1d18; --alta:#b3261e; --alta-bg:#fdecea; --media:#8a5a00;
  --media-bg:#fdf3e0; --baja:#3f6212; --baja-bg:#f0f6e6;
}
html{-webkit-text-size-adjust:100%}
body{
  margin:0; background:var(--bg); color:var(--text);
  font:16px/1.65 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Roboto,"Helvetica Neue",Arial,sans-serif;
  -webkit-font-smoothing:antialiased;
}
nav.top{
  position:sticky; top:0; z-index:20; background:var(--surface);
  border-bottom:1px solid var(--border); backdrop-filter:blur(8px);
}
nav.top .inner{
  max-width:60rem; margin:0 auto; padding:.6rem 1.25rem;
  display:flex; align-items:center; gap:.35rem; flex-wrap:wrap;
}
nav.top .brand{
  font-weight:650; margin-right:auto; font-size:.9rem; letter-spacing:.01em;
}
nav.top .brand span{color:var(--muted); font-weight:400}
nav.top a{
  color:var(--muted); text-decoration:none; padding:.32rem .7rem;
  border-radius:6px; font-size:.875rem; white-space:nowrap;
}
nav.top a:hover{background:var(--accent-soft); color:var(--text)}
nav.top a.active{background:var(--accent-soft); color:var(--accent); font-weight:600}
main{max-width:60rem; margin:0 auto; padding:2.5rem 1.25rem 6rem}
h1,h2,h3,h4{line-height:1.25; font-weight:650; letter-spacing:-.011em}
h1{font-size:2rem; margin:.2em 0 .6em}
h2{
  font-size:1.35rem; margin:2.6em 0 .7em; padding-top:1.3em;
  border-top:1px solid var(--border);
}
main > h2:first-of-type{border-top:0; padding-top:0}
h3{font-size:1.08rem; margin:2em 0 .5em; color:var(--muted)}
p,li{overflow-wrap:break-word}
a{color:var(--accent)}
hr{border:0; border-top:1px solid var(--border); margin:2.5em 0}
blockquote{
  margin:1.4em 0; padding:.8em 1.1em; border-left:3px solid var(--accent);
  background:var(--accent-soft); border-radius:0 8px 8px 0; color:var(--muted);
}
blockquote p{margin:.3em 0}
code{
  font-family:ui-monospace,SFMono-Regular,"SF Mono",Menlo,Consolas,monospace;
  font-size:.86em; background:var(--code-bg); padding:.15em .4em;
  border-radius:4px; border:1px solid var(--border);
}
pre{
  background:var(--code-bg); border:1px solid var(--border); border-radius:10px;
  padding:1rem 1.1rem; overflow-x:auto; font-size:.85rem; line-height:1.55;
  box-shadow:var(--shadow);
}
pre code{background:none; border:0; padding:0; font-size:1em}
.tablewrap{overflow-x:auto; margin:1.5em 0; -webkit-overflow-scrolling:touch}
table{border-collapse:collapse; width:100%; font-size:.9rem; min-width:30rem}
th,td{padding:.6rem .8rem; text-align:left; border-bottom:1px solid var(--border);
  vertical-align:top}
th{
  font-weight:600; font-size:.76rem; text-transform:uppercase;
  letter-spacing:.05em; color:var(--muted); background:var(--surface);
  border-bottom:2px solid var(--border); white-space:nowrap;
}
tbody tr:hover{background:var(--accent-soft)}
td code{font-size:.82em}
.sev{
  display:inline-block; padding:.12em .55em; border-radius:99px;
  font-size:.74rem; font-weight:650; letter-spacing:.02em; white-space:nowrap;
}
.sev-critica{color:#fff; background:var(--critica)}
.sev-alta{color:var(--alta); background:var(--alta-bg)}
.sev-media{color:var(--media); background:var(--media-bg)}
.sev-baja{color:var(--baja); background:var(--baja-bg)}
.theme-toggle{
  background:none; border:1px solid var(--border); color:var(--muted);
  border-radius:6px; padding:.3rem .55rem; cursor:pointer; font-size:.85rem;
  line-height:1;
}
.theme-toggle:hover{background:var(--accent-soft); color:var(--text)}
ul,ol{padding-left:1.4em}
li{margin:.3em 0}
li input[type=checkbox]{margin-right:.5em}
@media(max-width:640px){
  main{padding:1.75rem 1rem 4rem} h1{font-size:1.6rem} h2{font-size:1.2rem}
  nav.top .brand{width:100%; margin-bottom:.3rem}
}
@media print{
  nav.top{display:none} main{max-width:none}
  pre,table{box-shadow:none} h2{page-break-after:avoid}
}
"""

JS = """
(function(){
  var r=document.documentElement, k='auditoria-theme', s=localStorage.getItem(k);
  if(s) r.setAttribute('data-theme',s);
  var b=document.getElementById('themeBtn');
  if(!b) return;
  function cur(){
    return r.getAttribute('data-theme') ||
      (matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');
  }
  function paint(){ b.textContent = cur()==='dark' ? 'Claro' : 'Oscuro'; }
  paint();
  b.addEventListener('click',function(){
    var n = cur()==='dark' ? 'light' : 'dark';
    r.setAttribute('data-theme',n); localStorage.setItem(k,n); paint();
  });
})();
"""


def severity_badges(html: str) -> str:
    """Convierte celdas de severidad en pastillas de color.

    La celda puede venir como <td>Alta</td> o, si el markdown la puso en
    negrita, como <td><strong>Alta</strong></td>.
    """
    slug = {"crítica": "critica", "alta": "alta", "media": "media", "baja": "baja"}

    def repl(m):
        word = m.group(1)
        return f'<td><span class="sev sev-{slug[word.lower()]}">{word}</span></td>'

    return re.sub(
        r"<td>(?:<strong>)?(Crítica|Alta|Media|Baja)(?:</strong>)?</td>", repl, html
    )


def wrap_tables(html: str) -> str:
    return html.replace("<table>", '<div class="tablewrap"><table>').replace(
        "</table>", "</table></div>"
    )


def fix_links(html: str) -> str:
    """ARQUITECTURA.md -> arquitectura.html, etc."""
    for src, dst, _ in PAGES:
        html = html.replace(f'href="{src}"', f'href="{dst}"')
        html = re.sub(rf'href="{src}#', f'href="{dst}#', html)
    return html


def nav(active: str) -> str:
    links = "".join(
        f'<a href="{dst}" class="{"active" if dst == active else ""}">{label}</a>'
        for _, dst, label in PAGES
    )
    return (
        '<nav class="top"><div class="inner">'
        '<div class="brand">Auditoría técnica <span>· reports-trimax</span></div>'
        f'{links}<button class="theme-toggle" id="themeBtn">Oscuro</button>'
        "</div></nav>"
    )


def gh_slug(value: str, separator: str) -> str:
    """Slug al estilo GitHub: conserva acentos, descarta puntuacion.

    Necesario para que los enlaces profundos de los .md (#i1--la-cache-...)
    apunten a los id que se generan aqui. El slugify por defecto de python-
    markdown quita los acentos y colapsa los guiones, y los enlaces se rompen.
    """
    value = value.strip().lower()
    value = re.sub(r"[^\w\s-]", "", value, flags=re.UNICODE)
    return re.sub(r"\s", separator, value)


md = markdown.Markdown(
    extensions=["tables", "fenced_code", "codehilite", "toc", "attr_list", "sane_lists"],
    extension_configs={
        "codehilite": {"noclasses": True, "pygments_style": "friendly"},
        "toc": {"slugify": gh_slug},
    },
)

for src, dst, label in PAGES:
    text = (BASE / src).read_text(encoding="utf-8")
    md.reset()
    body = md.convert(text)
    body = wrap_tables(severity_badges(body))
    body = fix_links(body)
    title = re.search(r"<h1[^>]*>(.*?)</h1>", body, re.S)
    title = re.sub(r"<[^>]+>", "", title.group(1)).strip() if title else label
    page = f"""<!doctype html>
<html lang="es"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{title} — reports-trimax</title>
<style>{CSS}</style>
</head><body>
{nav(dst)}
<main>{body}</main>
<script>{JS}</script>
</body></html>"""
    (OUT / dst).write_text(page, encoding="utf-8")
    print(f"  {OUT / dst}")

(OUT / ".gitignore").write_text("*\n", encoding="utf-8")
print("listo")
