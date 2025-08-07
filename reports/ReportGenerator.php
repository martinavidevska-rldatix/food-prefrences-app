<?php

namespace reports;

use PDO;
class ReportGenerator
{
    private PDO $pdo;
    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }
    public function generateReport(string $reportId): string
    {
          $sql = "SELECT f.name AS fruit_name, CONCAT(p.firstName, ' ', p.lastName) AS person_name
                    FROM fruits f
                    LEFT JOIN people_fruits pf ON pf.fruit_id = f.id
                    LEFT JOIN people p ON p.id = pf.person_id
                    ORDER BY f.name, person_name";

            $stmt = $this->pdo->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by fruit
            $fruitPeopleMap = [];

            foreach ($results as $row) {
                $fruit = $row['fruit_name'];
                $person = $row['person_name'] ?? '';
                $fruitPeopleMap[$fruit][] = $person ?: '—';
            }

            // Save CSV
            $reportDir = __DIR__ . '/../reports/generatedReports';
            if (!is_dir($reportDir)) {
                mkdir($reportDir, 0777, true);
            }
            $filePath = "{$reportDir}/{$reportId}.csv";
            $fp = fopen($filePath, 'w');

            fputcsv($fp, ['Fruit', 'People Who Like It']);

            foreach ($fruitPeopleMap as $fruit => $people) {
                $row = [$fruit, implode('; ', array_filter($people))];
                fputcsv($fp, $row);
            }

            fclose(stream: $fp);
            return $filePath;
    }
}