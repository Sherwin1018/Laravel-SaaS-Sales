<?php

namespace App\Support;

class XlsxWorkbookBuilder
{
    private const STYLE_INDEX = [
        'title' => 1,
        'summary' => 2,
        'header' => 3,
        'text' => 4,
        'wrap' => 5,
        'center' => 6,
        'currency' => 7,
        'status' => 8,
        'empty' => 9,
    ];

    public function __construct(
        private string $worksheetName,
        private string $title,
        private string $summaryLine,
        private array $headings,
        private array $widths,
        private array $rows,
    ) {
    }

    public function build(): string
    {
        $files = [
            '[Content_Types].xml' => $this->buildContentTypesXml(),
            '_rels/.rels' => $this->buildRootRelationshipsXml(),
            'docProps/app.xml' => $this->buildAppPropertiesXml(),
            'docProps/core.xml' => $this->buildCorePropertiesXml(),
            'xl/workbook.xml' => $this->buildWorkbookXml(),
            'xl/_rels/workbook.xml.rels' => $this->buildWorkbookRelationshipsXml(),
            'xl/styles.xml' => $this->buildStylesXml(),
            'xl/worksheets/sheet1.xml' => $this->buildWorksheetXml(),
        ];

        return $this->buildZipArchive($files);
    }

    private function buildContentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML;
    }

    private function buildRootRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML;
    }

    private function buildAppPropertiesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>OpenAI Codex</Application>
</Properties>
XML;
    }

    private function buildCorePropertiesXml(): string
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"'
            . ' xmlns:dc="http://purl.org/dc/elements/1.1/"'
            . ' xmlns:dcterms="http://purl.org/dc/terms/"'
            . ' xmlns:dcmitype="http://purl.org/dc/dcmitype/"'
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>OpenAI Codex</dc:creator>'
            . '<cp:lastModifiedBy>OpenAI Codex</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $timestamp . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $timestamp . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function buildWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>'
            . '<sheet name="' . $this->escapeXml($this->sanitizeWorksheetName($this->worksheetName)) . '" sheetId="1" r:id="rId1"/>'
            . '</sheets>'
            . '</workbook>';
    }

    private function buildWorkbookRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private function buildStylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="6">
    <font>
      <sz val="11"/>
      <color rgb="FF1F2937"/>
      <name val="Calibri"/>
      <family val="2"/>
    </font>
    <font>
      <b/>
      <sz val="16"/>
      <color rgb="FFFFFFFF"/>
      <name val="Calibri"/>
      <family val="2"/>
    </font>
    <font>
      <b/>
      <sz val="11"/>
      <color rgb="FFFFFFFF"/>
      <name val="Calibri"/>
      <family val="2"/>
    </font>
    <font>
      <b/>
      <sz val="11"/>
      <color rgb="FF1F2937"/>
      <name val="Calibri"/>
      <family val="2"/>
    </font>
    <font>
      <sz val="10"/>
      <color rgb="FF475467"/>
      <name val="Calibri"/>
      <family val="2"/>
    </font>
    <font>
      <i/>
      <sz val="11"/>
      <color rgb="FF667085"/>
      <name val="Calibri"/>
      <family val="2"/>
    </font>
  </fonts>
  <fills count="7">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1F4E78"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFEAF2F8"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF4F81BD"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFDCE6F1"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="3">
    <border>
      <left/><right/><top/><bottom/><diagonal/>
    </border>
    <border>
      <left/><right/><top/>
      <bottom style="thin"><color rgb="FFE5E7EB"/></bottom>
      <diagonal/>
    </border>
    <border>
      <left style="thin"><color rgb="FFD0D7DE"/></left>
      <right style="thin"><color rgb="FFD0D7DE"/></right>
      <top style="thin"><color rgb="FFD0D7DE"/></top>
      <bottom style="thin"><color rgb="FFD0D7DE"/></bottom>
      <diagonal/>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="10">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">
      <alignment horizontal="left" vertical="center" wrapText="1"/>
    </xf>
    <xf numFmtId="0" fontId="4" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">
      <alignment horizontal="left" vertical="center" wrapText="1"/>
    </xf>
    <xf numFmtId="0" fontId="2" fillId="4" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">
      <alignment vertical="top"/>
    </xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">
      <alignment vertical="top" wrapText="1"/>
    </xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="top"/>
    </xf>
    <xf numFmtId="4" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyNumberFormat="1" applyAlignment="1">
      <alignment horizontal="right" vertical="top"/>
    </xf>
    <xf numFmtId="0" fontId="3" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
    <xf numFmtId="0" fontId="5" fillId="6" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">
      <alignment vertical="center" wrapText="1"/>
    </xf>
  </cellXfs>
  <cellStyles count="1">
    <cellStyle name="Normal" xfId="0" builtinId="0"/>
  </cellStyles>
</styleSheet>
XML;
    }

    private function buildWorksheetXml(): string
    {
        $columnCount = max(1, count($this->headings));
        $lastColumn = $this->columnReference($columnCount);
        $bodyStartRow = 5;
        $lastDataRow = $bodyStartRow + max(count($this->rows), 1) - 1;
        $dimensionRef = 'A1:' . $lastColumn . $lastDataRow;

        $columnsXml = '';
        foreach (array_values($this->widths) as $index => $width) {
            $columnsXml .= '<col min="' . ($index + 1) . '" max="' . ($index + 1) . '" width="' . $this->normalizeColumnWidth($width) . '" customWidth="1"/>';
        }

        $rowsXml = '';
        $rowsXml .= $this->buildRowXml(1, [[
            'ref' => 'A1',
            'style' => 'title',
            'type' => 'String',
            'value' => $this->title,
        ]], 28);
        $rowsXml .= $this->buildRowXml(2, [[
            'ref' => 'A2',
            'style' => 'summary',
            'type' => 'String',
            'value' => $this->summaryLine,
        ]], 22);
        $rowsXml .= '<row r="3"/>';

        $headerCells = [];
        foreach (array_values($this->headings) as $index => $heading) {
            $headerCells[] = [
                'ref' => $this->columnReference($index + 1) . '4',
                'style' => 'header',
                'type' => 'String',
                'value' => (string) $heading,
            ];
        }
        $rowsXml .= $this->buildRowXml(4, $headerCells, 24);

        $mergeRefs = [
            'A1:' . $lastColumn . '1',
            'A2:' . $lastColumn . '2',
        ];

        if ($this->rows === []) {
            $rowsXml .= $this->buildRowXml($bodyStartRow, [[
                'ref' => 'A' . $bodyStartRow,
                'style' => 'empty',
                'type' => 'String',
                'value' => 'No rows matched the selected filters for this export.',
            ]], 22);
            if ($columnCount > 1) {
                $mergeRefs[] = 'A' . $bodyStartRow . ':' . $lastColumn . $bodyStartRow;
            }
        } else {
            foreach (array_values($this->rows) as $rowIndex => $row) {
                $cells = [];
                $sheetRow = $bodyStartRow + $rowIndex;

                foreach (array_values($row) as $cellIndex => $cell) {
                    $cells[] = [
                        'ref' => $this->columnReference($cellIndex + 1) . $sheetRow,
                        'style' => (string) ($cell['style'] ?? 'text'),
                        'type' => (string) ($cell['type'] ?? 'String'),
                        'value' => $cell['value'] ?? '',
                    ];
                }

                $rowsXml .= $this->buildRowXml($sheetRow, $cells);
            }
        }

        $mergeXml = '<mergeCells count="' . count($mergeRefs) . '">';
        foreach ($mergeRefs as $ref) {
            $mergeXml .= '<mergeCell ref="' . $ref . '"/>';
        }
        $mergeXml .= '</mergeCells>';

        $autoFilterXml = '<autoFilter ref="A4:' . $lastColumn . $lastDataRow . '"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<dimension ref="' . $dimensionRef . '"/>'
            . '<sheetViews>'
            . '<sheetView workbookViewId="0">'
            . '<pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/>'
            . '<selection pane="bottomLeft" activeCell="A5" sqref="A5"/>'
            . '</sheetView>'
            . '</sheetViews>'
            . '<sheetFormatPr defaultRowHeight="18"/>'
            . '<cols>' . $columnsXml . '</cols>'
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . $autoFilterXml
            . $mergeXml
            . '</worksheet>';
    }

    private function buildRowXml(int $rowNumber, array $cells, ?int $height = null): string
    {
        $rowXml = '<row r="' . $rowNumber . '"';
        if ($height !== null) {
            $rowXml .= ' ht="' . $height . '" customHeight="1"';
        }
        $rowXml .= '>';

        foreach ($cells as $cell) {
            $rowXml .= $this->buildCellXml(
                (string) ($cell['ref'] ?? ''),
                (string) ($cell['style'] ?? 'text'),
                (string) ($cell['type'] ?? 'String'),
                $cell['value'] ?? ''
            );
        }

        return $rowXml . '</row>';
    }

    private function buildCellXml(string $cellRef, string $style, string $type, mixed $value): string
    {
        $styleIndex = self::STYLE_INDEX[$style] ?? self::STYLE_INDEX['text'];

        if ($type === 'Number') {
            $numeric = is_numeric($value) ? (string) (0 + $value) : '0';

            return '<c r="' . $cellRef . '" s="' . $styleIndex . '"><v>' . $this->escapeXml($numeric) . '</v></c>';
        }

        return '<c r="' . $cellRef . '" s="' . $styleIndex . '" t="inlineStr"><is><t xml:space="preserve">'
            . $this->escapeXml($this->sanitizeText((string) $value))
            . '</t></is></c>';
    }

    private function buildZipArchive(array $files): string
    {
        $archive = '';
        $directory = '';
        $offset = 0;
        [$dosTime, $dosDate] = $this->dosTimestamp();
        $count = 0;

        foreach ($files as $path => $content) {
            $path = str_replace('\\', '/', $path);
            $content = (string) $content;
            $size = strlen($content);
            $pathLength = strlen($path);
            $crc = (int) sprintf('%u', crc32($content));

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $size,
                $size,
                $pathLength,
                0
            );

            $archive .= $localHeader . $path . $content;

            $directory .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $size,
                $size,
                $pathLength,
                0,
                0,
                0,
                0,
                0,
                $offset
            ) . $path;

            $offset = strlen($archive);
            $count++;
        }

        $directorySize = strlen($directory);
        $directoryOffset = strlen($archive);

        $archive .= $directory;
        $archive .= pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $count,
            $count,
            $directorySize,
            $directoryOffset,
            0
        );

        return $archive;
    }

    private function dosTimestamp(): array
    {
        $time = getdate();

        $dosTime = (($time['hours'] & 0x1F) << 11)
            | (($time['minutes'] & 0x3F) << 5)
            | ((int) floor(($time['seconds'] ?? 0) / 2) & 0x1F);

        $dosDate = ((max(1980, $time['year']) - 1980) << 9)
            | (($time['mon'] & 0x0F) << 5)
            | ($time['mday'] & 0x1F);

        return [$dosTime, $dosDate];
    }

    private function columnReference(int $index): string
    {
        $index = max(1, $index);
        $reference = '';

        while ($index > 0) {
            $index--;
            $reference = chr(65 + ($index % 26)) . $reference;
            $index = intdiv($index, 26);
        }

        return $reference;
    }

    private function normalizeColumnWidth(mixed $width): string
    {
        $numeric = max(45, (float) $width);

        return number_format(round($numeric / 7, 2), 2, '.', '');
    }

    private function sanitizeWorksheetName(string $value): string
    {
        $sanitized = preg_replace('/[:\\\\\\/\\?\\*\\[\\]]+/', ' ', $value) ?? $value;
        $sanitized = trim($sanitized);

        return mb_substr($sanitized !== '' ? $sanitized : 'Worksheet', 0, 31);
    }

    private function sanitizeText(string $value): string
    {
        return preg_replace('/[^\P{C}\t\n\r]/u', '', $value) ?? $value;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
