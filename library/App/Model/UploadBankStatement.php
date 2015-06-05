<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PullFileData
 *
 * @author brijesh
 */
class UploadBankStatement {

    private $head = array();
    private $lines = array();
    private $statements = array();

    const STATEMENT_HEAD_STR = 'Inst-No Cr/Dr';
    const STATEMENT_END_STR = 'Thank you for taking';
    const DELIMITER = '|';

    function __construct($file) {
        $this->lines = self::file2Lines($file);
        $statementHeadKey = self::searchStringInArray(self::STATEMENT_HEAD_STR, $this->lines);
        $statementEndKey = self::searchStringInArray(self::STATEMENT_END_STR, $this->lines);
        if ($statementHeadKey && $statementEndKey) {
            $statementStartKey = $statementHeadKey + 2; //Statement Start Form Next 2 Row From Head  
            $statementEndKey = $statementEndKey - 1; //Statement End Before 2 Rows
            $this->setHead($statementHeadKey);
            $this->setStatement($statementStartKey, $statementEndKey);
        }
    }

    public static function file2Lines($file) {
        $lines = file($file);
        $filterLines = self::trimArray($lines);
        return $filterLines;
    }

    public static function trimArray($arr) {
        $patterns = array("/\s +/"); //Add extra space for after 's' for neglect 1 space 
        $replacer = array(self::DELIMITER);
        $replaceArr = preg_replace($patterns, $replacer, $arr);
        $arr = array_values(array_filter(array_map('trim', $replaceArr)));

        return $arr;
    }

    static function searchStringInArray($searchStr, $arr) {
        foreach ($arr as $key => $val) {
            if (stripos($val, $searchStr)) {
                return $key;
            }
        }
    }

    private function setHead($headKey) {
        $headStr = $this->lines[$headKey];
        $headArr = explode(self::DELIMITER, $headStr);
        $this->head = array_values(self::trimArray($headArr));
    }

    private function setStatement($statementStartKey, $statementEndKey) {
        $statementArrs = $this->getStatementByKeys($statementStartKey, $statementEndKey);
        $statements = array();

        foreach ($statementArrs as $statementArr) {
            $expStatementArr = explode(self::DELIMITER, $statementArr);
            $statementVal = array_values(self::trimArray($expStatementArr));
            $statements[] = $this->combineStatement($statementVal);
        }
        $this->statements = $statements;
    }

    private function combineStatement($statementVal) {
        $expstatementVal = explode(' ', $statementVal[0]);
        unset($statementVal[0]); // Now Unset First Key
        if (count($expstatementVal) > 2) {
            $newExpstatementVal[] = $expstatementVal[0];
            unset($expstatementVal[0]); // Now Unset First Key
            $newExpstatementVal[] = implode(' ', $expstatementVal);
            $newstatementVal = array_merge($newExpstatementVal, $statementVal);
        } else {
            $newstatementVal = array_merge($expstatementVal, $statementVal);
        }

        return array_combine($this->head, $newstatementVal);
    }

    private function getStatementByKeys($startKey, $endKey) {
        $arr = array();
        for ($i = $startKey; $i <= $endKey; $i++) {
            $arr[] = $this->lines[$i];
        }
        return $arr;
    }

    public function getHead() {
        return $this->head;
    }

    public function getStatements() {
        return $this->statements;
    }

}
