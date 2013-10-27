
PHP_OUTDIR = objects
SQL_OUTDIR = objects

SRCFILES = schema.xml

PHP_FILES := $(SRCFILES:.xml=.php)
SQL_FILES := $(SRCFILES:.xml=.sql)
SQLITE_FILES := $(SRCFILES:.xml=.sqlite)

OUT_FILES := $(foreach file, ${PHP_FILES},${PHP_OUTDIR}/$(file)) 
OUT_FILES += $(foreach file, ${SQL_FILES},${SQL_OUTDIR}/$(file))
OUT_FILES += $(foreach file, ${SQLITE_FILES},${SQL_OUTDIR}/$(file))

TESTS = $(wildcard test*.php)
PHP   = php


.SUFFIXES: .php .xml .sql .sqlite
.PHONY: $(TESTS)

CDIR=../compiler/

${PHP_OUTDIR}/%.php: %.xml
	xsltproc -o $@ ${CDIR}genphp.xsl $< 

${SQL_OUTDIR}/%.sql: %.xml
	xsltproc -o $@ ${CDIR}gensql.xsl $< 

${SQL_OUTDIR}/%.sqlite: %.xml ${CDIR}gensqlite.xsl
	xsltproc -o $@ ${CDIR}gensqlite.xsl $< 



all: ${OUT_FILES}
	php testRunner.php

clean:
	${RM} ${OUT_FILES}


$(TESTS):
	$(PHP) testRunner.php $@
