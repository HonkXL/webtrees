<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2021 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Fisharebest\Webtrees;

use Ramsey\Uuid\Uuid;

use function array_filter;
use function str_contains;

/**
 * Static GEDCOM data for tags
 */
class GedcomTag
{
    /** All tags that webtrees knows how to translate - including special/internal tags */
    private const ALL_TAGS = [
        'ABBR',
        'ADDR',
        'ADR1',
        'ADR2',
        'ADOP',
        'ADOP:DATE',
        'ADOP:PLAC',
        'AFN',
        'AGE',
        'AGNC',
        'ALIA',
        'ANCE',
        'ANCI',
        'ANUL',
        'ASSO',
        'AUTH',
        'BAPL',
        'BAPL:DATE',
        'BAPL:PLAC',
        'BAPM',
        'BAPM:DATE',
        'BAPM:PLAC',
        'BARM',
        'BARM:DATE',
        'BARM:PLAC',
        'BASM',
        'BASM:DATE',
        'BASM:PLAC',
        'BIRT',
        'BIRT:DATE',
        'BIRT:PLAC',
        'BLES',
        'BLES:DATE',
        'BLES:PLAC',
        'BLOB',
        'BURI',
        'BURI:DATE',
        'BURI:PLAC',
        'CALN',
        'CAST',
        'CAUS',
        'CEME',
        'CENS',
        'CENS:DATE',
        'CENS:PLAC',
        'CHAN',
        'CHAN:DATE',
        'CHAN:_WT_USER',
        'CHAR',
        'CHIL',
        'CHR',
        'CHR:DATE',
        'CHR:PLAC',
        'CHRA',
        'CITN',
        'CITY',
        'COMM',
        'CONC',
        'CONT',
        'CONF',
        'CONF:DATE',
        'CONF:PLAC',
        'CONL',
        'COPR',
        'CORP',
        'CREM',
        'CREM:DATE',
        'CREM:PLAC',
        'CTRY',
        'DATA',
        'DATA:DATE',
        'DATE',
        'DEAT',
        'DEAT:CAUS',
        'DEAT:DATE',
        'DEAT:PLAC',
        'DESC',
        'DESI',
        'DEST',
        'DIV',
        'DIVF',
        'DSCR',
        'EDUC',
        'EDUC:AGNC',
        'EMAI',
        'EMAIL',
        'EMAL',
        'EMIG',
        'EMIG:DATE',
        'EMIG:PLAC',
        'ENDL',
        'ENDL:DATE',
        'ENDL:PLAC',
        'ENGA',
        'ENGA:DATE',
        'ENGA:PLAC',
        'EVEN',
        'EVEN:DATE',
        'EVEN:PLAC',
        'EVEN:TYPE',
        'FACT',
        'FACT:TYPE',
        'FAM',
        'FAMC',
        'FAMF',
        'FAMS',
        'FAX',
        'FCOM',
        'FCOM:DATE',
        'FCOM:PLAC',
        'FILE',
        'FONE',
        'FORM',
        'GEDC',
        'GIVN',
        'GRAD',
        'HEAD',
        'HUSB',
        'IDNO',
        'IMMI',
        'IMMI:DATE',
        'IMMI:PLAC',
        'INDI',
        'INFL',
        'LANG',
        'LATI',
        'LEGA',
        'LONG',
        'MAP',
        'MARB',
        'MARB:DATE',
        'MARB:PLAC',
        'MARR_CIVIL',
        'MARR_PARTNERS',
        'MARR_RELIGIOUS',
        'MARR_UNKNOWN',
        'MARC',
        'MARL',
        'MARR',
        'MARR:DATE',
        'MARR:PLAC',
        'MARS',
        'MEDI',
        'NAME',
        'NAME:FONE',
        'NAME:_HEB',
        'NATI',
        'NATU',
        'NATU:DATE',
        'NATU:PLAC',
        'NCHI',
        'NICK',
        'NMR',
        'NOTE',
        'NPFX',
        'NSFX',
        'OBJE',
        'OCCU',
        'OCCU:AGNC',
        'ORDI',
        'ORDN',
        'ORDN:AGNC',
        'ORDN:DATE',
        'ORDN:PLAC',
        'PAGE',
        'PEDI',
        'PHON',
        'PLAC',
        'PLAC:FONE',
        'PLAC:ROMN',
        'PLAC:_HEB',
        'POST',
        'PROB',
        'PROP',
        'PUBL',
        'QUAY',
        'REFN',
        'RELA',
        'RELI',
        'REPO',
        'RESI',
        'RESI:DATE',
        'RESI:PLAC',
        'RESN',
        'RETI',
        'RETI:AGNC',
        'RFN',
        'RIN',
        'ROLE',
        'ROMN',
        'SERV',
        'SEX',
        'SHARED_NOTE',
        'SLGC',
        'SLGC:DATE',
        'SLGC:PLAC',
        'SLGS',
        'SLGS:DATE',
        'SLGS:PLAC',
        'SOUR',
        'SPFX',
        'SSN',
        'STAE',
        'STAT',
        'STAT:DATE',
        'SUBM',
        'SUBN',
        'SURN',
        'TEMP',
        'TEXT',
        'TIME',
        'TITL',
        'TITL:FONE',
        'TITL:ROMN',
        'TITL:_HEB',
        'TRLR',
        'TYPE',
        'URL',
        'VERS',
        'WIFE',
        'WILL',
        'WWW',
        '_ADOP_CHIL',
        '_ADOP_GCHI',
        '_ADOP_GCH1',
        '_ADOP_GCH2',
        '_ADOP_HSIB',
        '_ADOP_SIBL',
        '_ADPF',
        '_ADPM',
        '_AKA',
        '_AKAN',
        '_ASSO',
        '_BAPM_CHIL',
        '_BAPM_GCHI',
        '_BAPM_GCH1',
        '_BAPM_GCH2',
        '_BAPM_HSIB',
        '_BAPM_SIBL',
        '_BIBL',
        '_BIRT_CHIL',
        '_BIRT_GCHI',
        '_BIRT_GCH1',
        '_BIRT_GCH2',
        '_BIRT_HSIB',
        '_BIRT_SIBL',
        '_BRTM',
        '_BRTM:DATE',
        '_BRTM:PLAC',
        '_BURI_CHIL',
        '_BURI_GCHI',
        '_BURI_GCH1',
        '_BURI_GCH2',
        '_BURI_GPAR',
        '_BURI_HSIB',
        '_BURI_SIBL',
        '_BURI_SPOU',
        '_CHR_CHIL',
        '_CHR_GCHI',
        '_CHR_GCH1',
        '_CHR_GCH2',
        '_CHR_HSIB',
        '_CHR_SIBL',
        '_COML',
        '_CREM_CHIL',
        '_CREM_GCHI',
        '_CREM_GCH1',
        '_CREM_GCH2',
        '_CREM_GPAR',
        '_CREM_HSIB',
        '_CREM_SIBL',
        '_CREM_SPOU',
        '_DATE',
        '_DBID',
        '_DEAT_CHIL',
        '_DEAT_GCHI',
        '_DEAT_GCH1',
        '_DEAT_GCH2',
        '_DEAT_GPAR',
        '_DEAT_GPA1',
        '_DEAT_GPA2',
        '_DEAT_HSIB',
        '_DEAT_PARE',
        '_DEAT_SIBL',
        '_DEAT_SPOU',
        '_DEG',
        '_DETS',
        '_DNA',
        '_EMAIL',
        '_EYEC',
        '_FA1',
        '_FA2',
        '_FA3',
        '_FA4',
        '_FA5',
        '_FA6',
        '_FA7',
        '_FA8',
        '_FA9',
        '_FA10',
        '_FA11',
        '_FA12',
        '_FA13',
        '_FNRL',
        '_FREL',
        '_GEDF',
        '_GODP',
        '_GOV',
        '_HAIR',
        '_HEB',
        '_HEIG',
        '_HNM',
        '_HOL',
        '_INTE',
        '_LOC',
        '_MARI',
        '_MARNM',
        '_PRIM',
        '_MARNM_SURN',
        '_MARR_CHIL',
        '_MARR_FAMC',
        '_MARR_GCHI',
        '_MARR_GCH1',
        '_MARR_GCH2',
        '_MARR_HSIB',
        '_MARR_PARE',
        '_MARR_SIBL',
        '_MBON',
        '_MDCL',
        '_MEDC',
        '_MEND',
        '_MILI',
        '_MILT',
        '_MREL',
        '_MSTAT',
        '_NAME',
        '_NAMS',
        '_NLIV',
        '_NMAR',
        '_NMR',
        '_PLACE',
        '_WT_USER',
        '_PRMN',
        '_SCBK',
        '_SEPR',
        '_SSHOW',
        '_STAT',
        '_SUBQ',
        '_TODO',
        '_TYPE',
        '_UID',
        '_URL',
        '_WEIG',
        '_WITN',
        '_YART',
        '__BRTM_CHIL',
        '__BRTM_GCHI',
        '__BRTM_GCH1',
        '__BRTM_GCH2',
        '__BRTM_HSIB',
        '__BRTM_SIBL',
        // These pseudo-tags are generated dynamically to display media object attributes
        '__FILE_SIZE__',
        '__IMAGE_SIZE__',
    ];

    /**
     * Is $tag one of our known tags?
     *
     * @param string $tag
     *
     * @return bool
     */
    public static function isTag($tag): bool
    {
        return in_array($tag, self::ALL_TAGS, true);
    }

    /**
     * Translate a tag, for an (optional) record
     *
     * @param string $tag
     *
     * @return string
     */
    public static function getLabel($tag): string
    {
        return Registry::elementFactory()->make($tag)->label();
    }

    /**
     * Translate a label/value pair, such as “Occupation: Farmer”
     *
     * @param string            $tag
     * @param string            $value
     * @param GedcomRecord|null $record
     * @param string|null       $element
     *
     * @return string
     */
    public static function getLabelValue(string $tag, string $value, GedcomRecord $record = null, $element = 'div'): string
    {
        return
            '<' . $element . ' class="fact_' . $tag . '">' .
            /* I18N: a label/value pair, such as “Occupation: Farmer”. Some languages may need to change the punctuation. */
            I18N::translate('<span class="label">%1$s:</span> <span class="field" dir="auto">%2$s</span>', self::getLabel($tag), $value) .
            '</' . $element . '>';
    }

    /**
     * Get a list of facts, for use in the "fact picker" edit control
     *
     * @param string $fact_type
     *
     * @return array<string>
     */
    public static function getPicklistFacts(string $fact_type): array
    {
        switch ($fact_type) {
            case Individual::RECORD_TYPE:
                $tags = [
                    // Facts, attributes for individuals (no links to FAMs)
                    'RESN',
                    'NAME',
                    'SEX',
                    'BIRT',
                    'CHR',
                    'DEAT',
                    'BURI',
                    'CREM',
                    'ADOP',
                    'BAPM',
                    'BARM',
                    'BASM',
                    'BLES',
                    'CHRA',
                    'CONF',
                    'FCOM',
                    'ORDN',
                    'NATU',
                    'EMIG',
                    'IMMI',
                    'CENS',
                    'PROB',
                    'WILL',
                    'GRAD',
                    'RETI',
                    'EVEN',
                    'CAST',
                    'DSCR',
                    'EDUC',
                    'IDNO',
                    'NATI',
                    'NCHI',
                    'NMR',
                    'OCCU',
                    'PROP',
                    'RELI',
                    'RESI',
                    'SSN',
                    'TITL',
                    'FACT',
                    'BAPL',
                    'CONL',
                    'ENDL',
                    'SLGC',
                    'SUBM',
                    'ASSO',
                    'ALIA',
                    'ANCI',
                    'DESI',
                    'RFN',
                    'AFN',
                    'REFN',
                    'RIN',
                    'CHAN',
                    'NOTE',
                    'SHARED_NOTE',
                    'SOUR',
                    'OBJE',
                    // non standard tags
                    '_BRTM',
                    '_DEG',
                    '_DNA',
                    '_EYEC',
                    '_FNRL',
                    '_HAIR',
                    '_HEIG',
                    '_HNM',
                    '_HOL',
                    '_INTE',
                    '_MDCL',
                    '_MEDC',
                    '_MILI',
                    '_MILT',
                    '_NAME',
                    '_NAMS',
                    '_NLIV',
                    '_NMAR',
                    '_PRMN',
                    '_TODO',
                    '_UID',
                    '_WEIG',
                    '_YART',
                ];
                break;

            case Family::RECORD_TYPE:
                $tags = [
                    // Facts for families, left out HUSB, WIFE & CHIL links
                    'RESN',
                    'ANUL',
                    'CENS',
                    'DIV',
                    'DIVF',
                    'ENGA',
                    'MARB',
                    'MARC',
                    'MARR',
                    'MARL',
                    'MARS',
                    'RESI',
                    'EVEN',
                    'NCHI',
                    'SUBM',
                    'SLGS',
                    'REFN',
                    'RIN',
                    'CHAN',
                    'NOTE',
                    'SHARED_NOTE',
                    'SOUR',
                    'OBJE',
                    // non standard tags
                    '_NMR',
                    'MARR_CIVIL',
                    'MARR_RELIGIOUS',
                    'MARR_PARTNERS',
                    'MARR_UNKNOWN',
                    '_COML',
                    '_MBON',
                    '_MARI',
                    '_SEPR',
                    '_TODO',
                ];
                break;

            case Source::RECORD_TYPE:
                $tags = [
                    // Facts for sources
                    'DATA',
                    'AUTH',
                    'TITL',
                    'ABBR',
                    'PUBL',
                    'TEXT',
                    'REPO',
                    'REFN',
                    'RIN',
                    'CHAN',
                    'NOTE',
                    'SHARED_NOTE',
                    'OBJE',
                    'RESN',
                ];
                break;

            case Repository::RECORD_TYPE:
                $tags = [
                    // Facts for repositories
                    'NAME',
                    'ADDR',
                    'PHON',
                    'EMAIL',
                    'FAX',
                    'WWW',
                    'NOTE',
                    'SHARED_NOTE',
                    'REFN',
                    'RIN',
                    'CHAN',
                    'RESN',
                ];
                break;

            case 'PLAC':
                $tags = [
                    // Facts for places
                    'FONE',
                    'ROMN',
                    // non standard tags
                    '_GOV',
                    '_HEB',
                ];
                break;

            case 'NAME':
                $tags = [
                    // Facts subordinate to NAME
                    'FONE',
                    'ROMN',
                    // non standard tags
                    '_HEB',
                    '_AKA',
                    '_MARNM',
                ];
                break;

            default:
                $tags = [];
                break;
        }

        $facts = [];
        foreach ($tags as $tag) {
            $facts[$tag] = self::getLabel($tag);
        }
        uasort($facts, '\Fisharebest\Webtrees\I18N::strcasecmp');

        return $facts;
    }

    /**
     * Translate the value for 1 FILE/2 FORM/3 TYPE
     *
     * @param string $type
     *
     * @return string
     */
    public static function getFileFormTypeValue(string $type): string
    {
        $element = Registry::elementFactory()->make('OBJE:FILE:FORM:TYPE');

        return $element->values()[$type] ?? $type;
    }

    /**
     * A list of all possible values for 1 FILE/2 FORM/3 TYPE
     *
     * @return string[]
     */
    public static function getFileFormTypes(): array
    {
        $element = Registry::elementFactory()->make('OBJE:FILE:FORM:TYPE');

        return array_filter($element->values());
    }

    /**
     * Generate a value for a new _UID field.
     * Instead of RFC4122-compatible UUIDs, generate ones that
     * are compatible with PAF, Legacy, RootsMagic, etc.
     * In these, the string is upper-cased, dashes are removed,
     * and a two-byte checksum is added.
     *
     * @return string
     */
    public static function createUid(): string
    {
        $uid = str_replace('-', '', Uuid::uuid4()->toString());

        $checksum_a = 0; // a sum of the bytes
        $checksum_b = 0; // a sum of the incremental values of $checksum_a

        // Compute checksums
        for ($i = 0; $i < 32; $i += 2) {
            $checksum_a += hexdec(substr($uid, $i, 2));
            $checksum_b += $checksum_a & 0xff;
        }

        return strtoupper($uid . substr(dechex($checksum_a), -2) . substr(dechex($checksum_b), -2));
    }
}
