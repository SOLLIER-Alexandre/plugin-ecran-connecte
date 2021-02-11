<?php

namespace Controllers;

use Exception;

/**
 * Class Controller
 *
 * Main Controller contain all basics functions
 *
 * @package Controllers
 */
class Controller
{

    /**
     * Explode the url by /
     *
     * @return array
     */
    public function getPartOfUrl()
    {
        $url = $_SERVER['REQUEST_URI'];
        $urlExplode = explode('/', $url);
        $cleanUrl = array();
        for($i = 0; $i < sizeof($urlExplode); ++$i) {
            if($urlExplode[$i] != '/' && $urlExplode[$i] != '') {
                $cleanUrl[] = $urlExplode[$i];
            }
        }
        return $cleanUrl;
    }

    /**
     * Write errors in a log file
     *
     * @param $event    string
     */
    public function addLogEvent($event)
    {
        $time = date("D, d M Y H:i:s");
        $time = "[" . $time . "] ";
        $event = $time . $event . "\n";
        file_put_contents(ABSPATH . TV_PLUG_PATH . "fichier.log", $event, FILE_APPEND);
    }

    /**
     * Get the url to upload a ics file
     *
     * @param $code     int
     *
     * @return string
     */
    public function getUrl($code)
    {
        $str = strtotime("now");
        $str2 = strtotime(date("Y-m-d", strtotime('now')) . " +6 day");
        $start = date('Y-m-d', $str);
        $end = date('Y-m-d', $str2);
        $url = 'https://ade-web-consult.univ-amu.fr/jsp/custom/modules/plannings/anonymous_cal.jsp?projectId=8&resources=' . $code . '&calType=ical&firstDate=' . $start . '&lastDate=' . $end;
        return $url;
    }

    /**
     * Get the path of a code
     *
     * @param $code     int
     *
     * @return string
     */
    public function getFilePath($code)
    {
        $filepath = ABSPATH . TV_ICSFILE_PATH;
        if (file_exists($filepath . "file0/" . $code.'.ics') && filesize($filepath . "file0/" . $code.'.ics') > 120) {
            $path = ABSPATH . TV_ICSFILE_PATH . "file0/" . $code.'.ics';
        } else if (file_exists($filepath . "file1/" . $code.'.ics') && filesize($filepath . "file1/" . $code.'.ics') > 120) {
            $path = ABSPATH . TV_ICSFILE_PATH . "file1/" . $code.'.ics';
            copy($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file1/' . $code.'.ics', $_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file0/' . $code.'.ics');
        } else if (file_exists($filepath . "file2/" . $code.'.ics') && filesize($filepath . "file2/" . $code.'.ics') > 120) {
            $path = $filepath . "file2/" . $code.'.ics';
            copy($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file2/' . $code.'.ics', $_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file0/' . $code.'.ics');
        } else if (file_exists($filepath . "file3/" . $code.'.ics') && filesize($filepath . "file3/" . $code.'.ics') > 120) {
            $path = $filepath . "file3/" . $code.'.ics';
            copy($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file3/' . $code.'.ics', $_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file0/' . $code.'.ics');
        } else {
            $this->addFile($code);
            $path = $filepath . "file0/" . $code.'.ics';
        }
        return $path;
    }

    /**
     * Upload a ics file
     *
     * @param $code     int Code ADE
     */
    public function addFile($code)
    {
        try {
            $path = ABSPATH . TV_ICSFILE_PATH . "file0/" . $code.'.ics';
            $url = $this->getUrl($code);
            //file_put_contents($path, fopen($url, 'r'));
            $contents = '';
            if (($handler = @fopen($url, "r")) !== FALSE) {
                while (!feof($handler)) {
                    $contents .= fread($handler, 8192);
                }
                fclose($handler);
            } else {
                throw new Exception('File open failed.');
            }
            if ($handle = fopen($path, "w")) {
                fwrite($handle, $contents);
                fclose($handle);
            } else {
                throw new Exception('File open failed.');
            }
        } catch (Exception $e) {
            $this->addLogEvent($e);
        }
    }

	/**
	 * Check if the input is a date
	 *
	 * @param $date
	 *
	 * @return bool
	 */
	public function isRealDate($date)
	{
		if (false === strtotime($date)) {
			return false;
		}
		list($year, $month, $day) = explode('-', $date);
		return checkdate($month, $day, $year);
	}
}
