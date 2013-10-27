<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">



  <xsl:include href="gensql.xsl"/>

  <xsl:template match="column[@type='text' and @default = '']" mode="default-value"/>
  <xsl:template match="column[@type='text' and count(@default) = 0]" mode="default-value"/>

  

</xsl:stylesheet>
