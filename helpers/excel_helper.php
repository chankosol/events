<?php

/**
 * ExcelHelper - Professional XLSX Spreadsheet Generator
 * Generates native Microsoft Excel (.xlsx) files with rich styling,
 * custom fonts (Khmer Unicode), cell colors, borders, and column widths.
 * Zero third-party dependencies. Uses ZipArchive or PureZip fallback.
 */

class SimpleXlsx {
    private array $rows = [];
    private array $merges = [];
    private array $colWidths = [];
    private string $sheetName = 'បញ្ជីសិក្ខាកាម';

    public function setSheetName(string $name): self {
        $clean = preg_replace('/[\[\]\*\?\/\\:]/', '', $name);
        $this->sheetName = mb_substr($clean, 0, 31, 'UTF-8') ?: 'Sheet1';
        return $this;
    }

    public function setColWidths(array $widths): self {
        $this->colWidths = $widths;
        return $this;
    }

    public function addRow(array $cells, int $height = 24): self {
        $this->rows[] = ['cells' => $cells, 'height' => $height];
        return $this;
    }

    public function mergeCells(string $range): self {
        $this->merges[] = strtoupper($range);
        return $this;
    }

    private function sanitizeXml(string $str): string {
        // Strip control characters not permitted in XML 1.0
        $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $str);
        return htmlspecialchars($str, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function colLetter(int $colIndex): string {
        $letter = '';
        while ($colIndex > 0) {
            $rem = ($colIndex - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $colIndex = intval(($colIndex - $rem) / 26);
        }
        return $letter;
    }

    public function build(): string {
        // 1. Build sheet1.xml
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        
        $sheetXml .= '  <sheetViews>' . "\n";
        $sheetXml .= '    <sheetView tabSelected="1" workbookViewId="0">' . "\n";
        $sheetXml .= '      <pane state="split"/>' . "\n";
        $sheetXml .= '      <selection/>' . "\n";
        $sheetXml .= '    </sheetView>' . "\n";
        $sheetXml .= '  </sheetViews>' . "\n";
        
        $sheetXml .= '  <sheetFormatPr defaultRowHeight="22" baseColWidth="10"/>' . "\n";

        // Column widths
        if (!empty($this->colWidths)) {
            $sheetXml .= '  <cols>' . "\n";
            $colNum = 1;
            foreach ($this->colWidths as $w) {
                $sheetXml .= '    <col min="' . $colNum . '" max="' . $colNum . '" width="' . $w . '" customWidth="1"/>' . "\n";
                $colNum++;
            }
            $sheetXml .= '  </cols>' . "\n";
        }

        // Sheet Data
        $sheetXml .= '  <sheetData>' . "\n";
        $rowNum = 1;
        foreach ($this->rows as $rData) {
            $height = $rData['height'];
            $cells = $rData['cells'];
            $sheetXml .= '    <row r="' . $rowNum . '" ht="' . $height . '" customHeight="1">' . "\n";

            $colNum = 1;
            foreach ($cells as $cell) {
                $ref = $this->colLetter($colNum) . $rowNum;
                $val = $cell['v'] ?? '';
                $style = $cell['s'] ?? 0;
                $type = $cell['t'] ?? 's'; // 's' = string, 'n' = number

                if ($val === '' || $val === null) {
                    $sheetXml .= '      <c r="' . $ref . '" s="' . $style . '"/>' . "\n";
                } elseif ($type === 'n') {
                    $numVal = is_numeric($val) ? (float)$val : 0;
                    $sheetXml .= '      <c r="' . $ref . '" s="' . $style . '"><v>' . $numVal . '</v></c>' . "\n";
                } else {
                    $escaped = $this->sanitizeXml((string)$val);
                    $sheetXml .= '      <c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>' . "\n";
                }
                $colNum++;
            }

            $sheetXml .= '    </row>' . "\n";
            $rowNum++;
        }
        $sheetXml .= '  </sheetData>' . "\n";

        // Merge cells
        if (!empty($this->merges)) {
            $sheetXml .= '  <mergeCells count="' . count($this->merges) . '">' . "\n";
            foreach ($this->merges as $m) {
                $sheetXml .= '    <mergeCell ref="' . $m . '"/>' . "\n";
            }
            $sheetXml .= '  </mergeCells>' . "\n";
        }

        $sheetXml .= '  <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $sheetXml .= '</worksheet>';

        // 2. Build styles.xml
        $stylesXml = $this->buildStylesXml();

        // 3. Build workbook.xml
        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $workbookXml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        $workbookXml .= '  <sheets>' . "\n";
        $workbookXml .= '    <sheet name="' . $this->sanitizeXml($this->sheetName) . '" sheetId="1" r:id="rId1"/>' . "\n";
        $workbookXml .= '  </sheets>' . "\n";
        $workbookXml .= '</workbook>';

        // 4. Relationships
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $wbRels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n";
        $wbRels .= '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' . "\n";
        $wbRels .= '  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' . "\n";
        $wbRels .= '</Relationships>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $rootRels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n";
        $rootRels .= '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' . "\n";
        $rootRels .= '</Relationships>';

        // 5. Content Types
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $contentTypes .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n";
        $contentTypes .= '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n";
        $contentTypes .= '  <Default Extension="xml" ContentType="application/xml"/>' . "\n";
        $contentTypes .= '  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n";
        $contentTypes .= '  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n";
        $contentTypes .= '  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . "\n";
        $contentTypes .= '</Types>';

        // Pack files
        $zipFiles = [
            '[Content_Types].xml' => $contentTypes,
            '_rels/.rels' => $rootRels,
            'xl/_rels/workbook.xml.rels' => $wbRels,
            'xl/workbook.xml' => $workbookXml,
            'xl/styles.xml' => $stylesXml,
            'xl/worksheets/sheet1.xml' => $sheetXml,
        ];

        return $this->createZip($zipFiles);
    }

    private function buildStylesXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <numFmts count="1">
    <numFmt numFmtId="164" formatCode="$#,##0.00"/>
  </numFmts>
  <fonts count="8">
    <!-- 0: Normal 11pt Khmer OS Siemreap -->
    <font><sz val="11"/><name val="Khmer OS Siemreap"/><family val="2"/></font>
    <!-- 1: Bold 11pt Khmer OS Siemreap -->
    <font><b/><sz val="11"/><name val="Khmer OS Siemreap"/><family val="2"/></font>
    <!-- 2: White Bold 11pt (Table Header) -->
    <font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Khmer OS Siemreap"/><family val="2"/></font>
    <!-- 3: Big White Bold 16pt (Main Title Banner) -->
    <font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Khmer OS Siemreap"/><family val="2"/></font>
    <!-- 4: White Bold 12pt (Event Name Subtitle) -->
    <font><b/><sz val="12"/><color rgb="FFFFFFFF"/><name val="Khmer OS Siemreap"/><family val="2"/></font>
    <!-- 5: Meta Label Gray Bold 10.5pt -->
    <font><b/><sz val="10.5"/><color rgb="FF334155"/><name val="Khmer OS Siemreap"/><family val="2"/></font>
    <!-- 6: Green Bold 10.5pt (Confirmed/Paid) -->
    <font><b/><sz val="10.5"/><color rgb="FF166534"/><name val="Khmer OS Siemreap"/><family val="2"/></font>
    <!-- 7: Gray Regular 10pt -->
    <font><sz val="10"/><color rgb="FF64748B"/><name val="Khmer OS Siemreap"/><family val="2"/></font>
  </fonts>
  <fills count="8">
    <!-- 0: None -->
    <fill><patternFill patternType="none"/></fill>
    <!-- 1: Gray125 -->
    <fill><patternFill patternType="gray125"/></fill>
    <!-- 2: Deep Blue Banner (#1E3A8A) -->
    <fill><patternFill patternType="solid"><fgColor rgb="FF1E3A8A"/></patternFill></fill>
    <!-- 3: Primary Blue Banner (#2563EB) -->
    <fill><patternFill patternType="solid"><fgColor rgb="FF2563EB"/></patternFill></fill>
    <!-- 4: Meta Box Background (#F1F5F9) -->
    <fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/></patternFill></fill>
    <!-- 5: Zebra Row Light (#F8FAFC) -->
    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>
    <!-- 6: Summary Row Background (#E2E8F0) -->
    <fill><patternFill patternType="solid"><fgColor rgb="FFE2E8F0"/></patternFill></fill>
    <!-- 7: White Fill (#FFFFFF) -->
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>
  </fills>
  <borders count="4">
    <!-- 0: None -->
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <!-- 1: Thin Gray (#CBD5E1) -->
    <border>
      <left style="thin"><color rgb="FFCBD5E1"/></left>
      <right style="thin"><color rgb="FFCBD5E1"/></right>
      <top style="thin"><color rgb="FFCBD5E1"/></top>
      <bottom style="thin"><color rgb="FFCBD5E1"/></bottom>
    </border>
    <!-- 2: Table Header Border -->
    <border>
      <left style="thin"><color rgb="FF1E40AF"/></left>
      <right style="thin"><color rgb="FF1E40AF"/></right>
      <top style="thin"><color rgb="FF1E40AF"/></top>
      <bottom style="medium"><color rgb="FF0F172A"/></bottom>
    </border>
    <!-- 3: Summary Bottom Double Border -->
    <border>
      <left style="thin"><color rgb="FF94A3B8"/></left>
      <right style="thin"><color rgb="FF94A3B8"/></right>
      <top style="thin"><color rgb="FF94A3B8"/></top>
      <bottom style="double"><color rgb="FF0F172A"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="16">
    <!-- 0: Default Normal -->
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <!-- 1: Title Banner (s=1): Font 3, Fill 2, Center/Center -->
    <xf numFmtId="0" fontId="3" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
    <!-- 2: Subtitle Banner (s=2): Font 4, Fill 3, Center/Center -->
    <xf numFmtId="0" fontId="4" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
    <!-- 3: Meta Label (s=3): Font 5, Fill 4, Border 1, Left/Center -->
    <xf numFmtId="0" fontId="5" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="left" vertical="center"/>
    </xf>
    <!-- 4: Meta Value (s=4): Font 1, Fill 7, Border 1, Left/Center -->
    <xf numFmtId="0" fontId="1" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="left" vertical="center" wrapText="1"/>
    </xf>
    <!-- 5: Table Header (s=5): Font 2, Fill 2, Border 2, Center/Center -->
    <xf numFmtId="0" fontId="2" fillId="2" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
    <!-- 6: Data Cell Left White (s=6): Font 0, Fill 7, Border 1 -->
    <xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="left" vertical="center"/>
    </xf>
    <!-- 7: Data Cell Center White (s=7): Font 0, Fill 7, Border 1 -->
    <xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <!-- 8: Data Cell Right White (s=8): Font 0, Fill 7, Border 1, Currency -->
    <xf numFmtId="164" fontId="0" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1">
      <alignment horizontal="right" vertical="center"/>
    </xf>
    <!-- 9: Data Cell Left Zebra (s=9): Font 0, Fill 5, Border 1 -->
    <xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="left" vertical="center"/>
    </xf>
    <!-- 10: Data Cell Center Zebra (s=10): Font 0, Fill 5, Border 1 -->
    <xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <!-- 11: Data Cell Right Zebra (s=11): Font 0, Fill 5, Border 1, Currency -->
    <xf numFmtId="164" fontId="0" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1">
      <alignment horizontal="right" vertical="center"/>
    </xf>
    <!-- 12: Summary Label (s=12): Font 1, Fill 6, Border 3, Right/Center -->
    <xf numFmtId="0" fontId="1" fillId="6" borderId="3" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="right" vertical="center"/>
    </xf>
    <!-- 13: Summary Value Currency (s=13): Font 1, Fill 6, Border 3, Right/Center -->
    <xf numFmtId="164" fontId="1" fillId="6" borderId="3" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1">
      <alignment horizontal="right" vertical="center"/>
    </xf>
    <!-- 14: Summary Value Text (s=14): Font 1, Fill 6, Border 3, Center/Center -->
    <xf numFmtId="0" fontId="1" fillId="6" borderId="3" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <!-- 15: Data Cell Bold Center White (s=15): Font 1, Fill 7, Border 1 -->
    <xf numFmtId="0" fontId="1" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
  </cellXfs>
  <cellStyles count="1">
    <cellStyle name="Normal" xfId="0" builtinId="0"/>
  </cellStyles>
</styleSheet>';
    }

    private function createZip(array $files): string {
        // Preferred: PHP ZipArchive
        if (class_exists('ZipArchive')) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'wos_xlsx_');
            $zip = new ZipArchive();
            if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($files as $name => $content) {
                    $zip->addFromString($name, $content);
                }
                $zip->close();
                $data = file_get_contents($tmpFile);
                @unlink($tmpFile);
                if (!empty($data)) {
                    return $data;
                }
            }
        }

        // Pure PHP ZIP Fallback
        $data = '';
        $cd = '';
        $offset = 0;

        foreach ($files as $path => $content) {
            $uncompressedSize = strlen($content);
            $crc32 = crc32($content);

            $compressedContent = function_exists('gzdeflate') ? gzdeflate($content) : $content;
            if ($compressedContent !== false && strlen($compressedContent) < $uncompressedSize) {
                $compMethod = 8;
                $compressedSize = strlen($compressedContent);
                $body = $compressedContent;
            } else {
                $compMethod = 0;
                $compressedSize = $uncompressedSize;
                $body = $content;
            }

            $fnLen = strlen($path);
            $lh = pack('VvvvVVVVvv',
                0x04034b50, 20, 0, $compMethod, 0, $crc32,
                $compressedSize, $uncompressedSize, $fnLen, 0
            );
            $data .= $lh . $path . $body;

            $cdEntry = pack('VvvvvVVVVvvvvvVV',
                0x02014b50, 20, 20, 0, $compMethod, 0, $crc32,
                $compressedSize, $uncompressedSize, $fnLen, 0, 0, 0, 0, 0,
                $offset
            );
            $cd .= $cdEntry . $path;
            $offset = strlen($data);
        }

        $eocd = pack('VvvvvVVv',
            0x06054b50, 0, 0, count($files), count($files),
            strlen($cd), $offset, 0
        );

        return $data . $cd . $eocd;
    }
}
