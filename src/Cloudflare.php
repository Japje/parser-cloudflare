<?php

namespace AbuseIO\Parsers;

use AbuseIO\Models\Incident;
use Log;

/**
 * Class Cloudflare
 * @package AbuseIO\Parsers
 */
class Cloudflare extends Parser
{
    /**
     * Create a new Cloudflare instance
     *
     * @param \PhpMimeMailParser\Parser $parsedMail phpMimeParser object
     * @param array $arfMail array with ARF detected results
     */
    public function __construct($parsedMail, $arfMail)
    {
        parent::__construct($parsedMail, $arfMail, $this);
    }

    /**
     * Parse attachments
     * @return array    Returns array with failed or success data
     *                  (See parser-common/src/Parser.php) for more info.
     */
    public function parse()
    {
        if ($this->arfMail !== true) {
            $this->feedName = 'default';

            // If feed is known and enabled, validate data and save report
            if ($this->isKnownFeed() && $this->isEnabledFeed()) {

                // To get some more consitency, remove "\r" from the report.
                $this->arfMail['report'] = str_replace("\r", "", $this->arfMail['report']);

                // Build up the report

                $re1='(The)'; # Word 1
                $re2='( )';   # White Space 1
                $re3='(actual)';  # Word 2
                $re4='( )';   # White Space 2
                $re5='(host)';    # Word 3
                $re6='( )';   # White Space 3
                $re7='(for)'; # Word 4
                $re8='( )';   # White Space 4
                $re9='((?:[a-z][a-z\\.\\d\\-]+)\\.(?:[a-z][a-z\\-]+))(?![\\w\\.])';   # Fully Qualified Domain Name 1
                $re10='( )';  # White Space 5
                $re11='(is)'; # Word 5
                $re12='( )';  # White Space 6
                $re13='((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?![\\d])';    # IPv4 IP Address 1
                $re14='(\\.)';    # Any Single Character 1
  
                if ($c=preg_match_all ("/".$re1.$re2.$re3.$re4.$re5.$re6.$re7.$re8.$re9.$re10.$re11.$re12.$re13.$re14."/is", $this->parsedMail->getMessageBody(), $matches))
                {
                    $word1=$matches[1][0];
                    $ws1=$matches[2][0];
                    $word2=$matches[3][0];
                    $ws2=$matches[4][0];
                    $word3=$matches[5][0];
                    $ws3=$matches[6][0];
                    $word4=$matches[7][0];
                    $ws4=$matches[8][0];
                    $fqdn1=$matches[9][0];
                    $ws5=$matches[10][0];
                    $word5=$matches[11][0];
                    $ws6=$matches[12][0];
                    $ipaddress1=$matches[13][0];
                    $c1=$matches[14][0];
                    #print "($word1) ($ws1) ($word2) ($ws2) ($word3) ($ws3) ($word4) ($ws4) ($fqdn1) ($ws5) ($word5) ($ws6) ($ipaddress1) ($c1) \n";
                }
  
                $report = [
                    'IP' => $ipaddress1,
                    'domain' => $fqdn1,
                    'timestamp' => time(),
                ];

                // Sanity check
                if ($this->hasRequiredFields($report) === true) {

                    ksort($report);

                    // incident has all requirements met, filter and add!
                    $report = $this->applyFilters($report);

                    $incident = new Incident();
                    $incident->source      = config("{$this->configBase}.parser.name");
                    $incident->source_id   = false;
                    $incident->ip          = $report['IP'];
                    $incident->domain      = $report['domain'];
                    $incident->class       = config("{$this->configBase}.feeds.{$this->feedName}.class");
                    $incident->type        = config("{$this->configBase}.feeds.{$this->feedName}.type");
                    $incident->timestamp   = $report['timestamp'];
                    $incident->information = json_encode($report);

                    $this->incidents[] = $incident;

                }
            }
        }
        return $this->success();
    }
}