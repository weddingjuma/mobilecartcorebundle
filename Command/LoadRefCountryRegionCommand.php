<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadRefCountryRegionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:ref:regions')
            ->setDescription('Parse CSV and load Country Regions into ref_country_region table')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages importing Country Regions into Mobile Cart:

<info>php %command.full_name%</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);

        $path = realpath(__DIR__ . '/../Resources/csv/country_regions.csv');
        $message = "Opening File: " . $path;
        $output->writeln($message);

        $fh = fopen($path, 'r');
        if ($fh !== false) {

            $message = 'File Opened. Truncating table';
            $output->writeln($message);

            $doctrine = $this->getContainer()->get('doctrine');
            $em = $doctrine->getManager();
            $conn = $em->getConnection();

            $tableName = 'ref_country_region';

            $truncateSql = "truncate {$tableName}";
            $truncateStmt = $conn->prepare($truncateSql);
            $truncateStmt->execute();

            $message = 'Table Truncated. Loading CSV';
            $output->writeln($message);

            while (($data = fgetcsv($fh))) {

                $country = $data[0];
                $regionCode = $data[1];
                $regionName = isset($data[2]) ? $data[2] : '';
                $regionType = isset($data[3]) ? $data[3] : '';

                if (is_int(strpos($regionName, '(')) && is_int(strpos($regionName, ')'))) {
                    $open = strpos($regionName, '(');
                    $close = strpos($regionName, ')') + 1;
                    $regionName = substr($regionName, 0, $open) . substr($regionName, $close);
                }

                $regionName = utf8_encode($regionName);
                $regionName = str_replace(chr(228), '', $regionName);

                $message = "Inserting: {$country}, {$regionCode}, {$regionName}, {$regionType}";
                $output->writeln($message);

                $insertSql = "insert into `{$tableName}`".
                    " (`country_code`,`region_code`,`region_name`,`region_type`)".
                    " values (?, ?, ?, ?)";

                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bindParam(1, $country, \PDO::PARAM_STR);
                $insertStmt->bindParam(2, $regionCode, \PDO::PARAM_STR);
                $insertStmt->bindParam(3, $regionName, \PDO::PARAM_STR);
                $insertStmt->bindParam(4, $regionType, \PDO::PARAM_STR);

                $retry = false;
                try {
                    $insertStmt->execute();
                } catch(\Exception $e) {
                    $retry = true;
                }

                if ($retry) {
                    // todo : reference : http://ascii-code.com/
                    $output->writeln("todo: retry: {$message}");
                }
            }

            fclose($fh);

        } else {
            $message = "Error : could not open file";
            $output->writeln($message);
        }
    }
}
