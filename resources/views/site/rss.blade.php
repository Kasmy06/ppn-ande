<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" encoding="UTF-8" doctype-system="about:legacy-compat"/>

  <xsl:template match="/rss/channel">
    <html lang="fr">
    <head>
      <meta charset="UTF-8"/>
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <title><xsl:value-of select="title"/></title>
      <style>
        body { font-family: Arial, Helvetica, sans-serif; background:#f6ecf8; color:#1e293b; margin:0; padding:0 20px 60px; }
        .wrap { max-width: 720px; margin: 0 auto; }
        header { padding: 40px 0 24px; }
        h1 { color:#3B0B4D; font-size: 1.7rem; margin: 0 0 6px; }
        .sub { color:#64748b; font-size: .95rem; }
        .notice { background:#fff; border:1px solid #e2e8f0; border-left:4px solid #1FA24B; border-radius:10px; padding:16px 20px; margin: 20px 0 32px; font-size:.88rem; line-height:1.5; }
        .notice code { background:#f1f5f9; padding:2px 6px; border-radius:6px; font-size:.85em; }
        .item { background:#fff; border:1px solid #e2e8f0; border-top:4px solid #8E2E8E; border-radius:12px; padding:16px 20px; margin-bottom:14px; }
        .item h2 { margin:0 0 4px; font-size:1.02rem; }
        .item h2 a { color:#3B0B4D; text-decoration:none; }
        .date { font-size:.74rem; color:#64748b; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; }
        .item p { white-space:pre-line; font-size:.9rem; margin:0; }
      </style>
    </head>
    <body>
      <div class="wrap">
        <header>
          <h1><xsl:value-of select="title"/></h1>
          <div class="sub"><xsl:value-of select="description"/></div>
        </header>
        <div class="notice">
          Ceci est un <strong>flux RSS</strong>, pas une page classique du site. Copiez l'adresse de cette page dans un lecteur
          de flux (Feedly, Inoreader, Thunderbird…) pour être averti automatiquement des nouvelles annonces. En attendant, en
          voici le contenu :
        </div>
        <xsl:for-each select="item">
          <article class="item">
            <div class="date"><xsl:value-of select="pubDate"/></div>
            <h2><a href="{link}"><xsl:value-of select="title"/></a></h2>
            <p><xsl:value-of select="description"/></p>
          </article>
        </xsl:for-each>
      </div>
    </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
