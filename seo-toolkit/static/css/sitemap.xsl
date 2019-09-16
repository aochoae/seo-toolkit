<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:html="http://www.w3.org/TR/REC-html40"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
    <xsl:template match="/">
        <xsl:variable name="sitemapType">
            <xsl:choose>
                <xsl:when test="sitemap:urlset/sitemap:url">sitemap</xsl:when>
                <xsl:otherwise>sitemapindex</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>
                <xsl:choose>
                    <xsl:when test="$sitemapType='sitemap'">Sitemap</xsl:when>
                    <xsl:otherwise>Sitemap Index</xsl:otherwise>
                </xsl:choose>
                </title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <style type="text/css">
                    body {
                        margin: 0;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                        font-size: 16px;
                    }
                    #header {
                        background-color: #03A9F4;
                        padding: 1rem 2rem;
                    }
                    #header h1,
                    #header p {
                        color: #fff;
                        font-size: 1rem;
                        margin: 0;
                    }
                    #header h1 {
                        font-size: 1.125rem;
                        padding-bottom: 1rem;
                    }
                    table {
                        font-size: 0.875rem;
                        margin: 1rem auto;
                        border: none;
                        border-collapse: collapse;
                        width: 80%;
                    }
                    th {
                        border-bottom: 1px solid #ccc;
                        text-align: left;
                        padding: 0.75rem 0.25rem;
                    }
                    td {
                        padding: 0.75rem 0.25rem;
                        border-left: 3px solid #fff;
                    }
                    table td a {
                        display: block;
                    }
                </style>
            </head>
            <body>
                <div id="content">
                    <div id="header">
                        <h1>XML Sitemap</h1>
                        <p>
                        <xsl:choose>
                            <xsl:when test="$sitemapType='sitemap'">This sitemap contains <xsl:value-of select="count(sitemap:urlset/sitemap:url)"></xsl:value-of> URLs.</xsl:when>
                            <xsl:otherwise>This sitemap index contains <xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"></xsl:value-of> sitemaps.</xsl:otherwise>
                        </xsl:choose>
                        </p>
                    </div>
                    <xsl:choose>
                        <xsl:when test="$sitemapType='sitemap'"><xsl:call-template name="sitemap"/></xsl:when>
                        <xsl:otherwise><xsl:call-template name="siteindex"/></xsl:otherwise>
                    </xsl:choose>
                </div>
            </body>
        </html>
    </xsl:template>
    <xsl:template name="siteindex">
        <table cellpadding="3">
            <thead>
            <tr>
                <th width="50%">URL</th>
                <th>Last Change</th>
            </tr>
            </thead>
            <tbody>
            <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                <tr>
                    <td>
                        <xsl:variable name="itemURL">
                            <xsl:value-of select="sitemap:loc"/>
                        </xsl:variable>
                        <a href="{$itemURL}"><xsl:value-of select="sitemap:loc"/></a>
                    </td>
                    <td><xsl:value-of select="sitemap:lastmod"/></td>
                </tr>
            </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
    <xsl:template name="sitemap">
        <table cellpadding="3">
            <thead>
            <tr>
                <th width="50%">URL</th>
                <th>Images</th>
                <th>Change Frequency</th>
                <th>Last Change</th>
            </tr>
            </thead>
            <tbody>
            <xsl:for-each select="sitemap:urlset/sitemap:url">
                <tr>
                    <td>
                        <xsl:variable name="itemURL">
                            <xsl:value-of select="sitemap:loc"/>
                        </xsl:variable>
                        <a href="{$itemURL}"><xsl:value-of select="sitemap:loc"/></a>
                    </td>
                    <td>
                        <xsl:value-of select="count(image:image)"/>
                    </td>
                    <xsl:choose>
                        <xsl:when test="sitemap:changefreq = 'always'"><td>Always</td></xsl:when>
                        <xsl:when test="sitemap:changefreq = 'hourly'"><td>Hourly</td></xsl:when>
                        <xsl:when test="sitemap:changefreq = 'daily'"><td>Daily</td></xsl:when>
                        <xsl:when test="sitemap:changefreq = 'weekly'"><td>Weekly</td></xsl:when>
                        <xsl:when test="sitemap:changefreq = 'monthly'"><td>Monthly</td></xsl:when>
                        <xsl:when test="sitemap:changefreq = 'yearly'"><td>Yearly</td></xsl:when>
                        <xsl:otherwise test="sitemap:changefreq = 'never'"><td>Never</td></xsl:otherwise>
                    </xsl:choose>
                    <td>
                        <xsl:value-of select="sitemap:lastmod"/>
                    </td>
                </tr>
            </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
</xsl:stylesheet>

