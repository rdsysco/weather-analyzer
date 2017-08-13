<?php

abstract class BaseAnalyzer {

    protected $records = array();
    protected $file;
    protected $columns = array();

    abstract public function prepareRecords();
    abstract protected function shouldSkip($column);
    abstract protected function getFilePath();

    public function findRecordWithLeastDiff(){
        $min = $this->records[0] ;
        foreach ($this->records as $teamInfo) {
            if($teamInfo->diff() < $min->diff()) {
                $min = $teamInfo;
            }
        }
        return $min;
    }

    protected function readFile()
    {
        $this->file->open($this->getFilePath());
        while (($line = $this->file->readLine()) !== false) {
            $column = preg_split("/\s+/", trim($line));
            if ($this->shouldSkip($column)) {
                continue;
            }
            $this->columns[] = $column;
        }
        $this->file->close();
    }
}

class TeamAnalyzer extends BaseAnalyzer {

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function prepareRecords() {
        $this->readFile();
        foreach ($this->columns as $column) {
            $this->records[] = new Record($column[6], $column[8], $column[1]);

        }

    }
    /**
     * @param $columns
     * @return bool
     */
    protected function shouldSkip($columns)
    {
        return !sizeof($columns) || !is_numeric($columns[0]) || !is_numeric($columns[6]) || !is_numeric($columns[8]);
    }

    protected function getFilePath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'football.dat';
    }
}




class WeatherInfoAnalyzer extends BaseAnalyzer {

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function prepareRecords() {
        $this->readFile();
        foreach ($this->columns as $column) {
            $this->records[] = new Record($column[1], $column[2], $column[0]);
        }
    }
    /**
     * @param $columns
     * @return bool
     */
    protected function shouldSkip($columns)
    {
        return !sizeof($columns) || !is_numeric($columns[0]) || !is_numeric($columns[1]) || !is_numeric($columns[2]);
    }

    protected function getFilePath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'weather.dat';
    }
}

class File {
    private $handle;
    private static $instance = null;
    private function __construct()
    {

    }

    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function open($path = null) {
        $this->handle = fopen($path, 'r');
    }

    public function readLine() {
        return fgets($this->handle);
    }

    public function close() {
        fclose($this->handle);
    }
}

class Record {
    private $start;
    private $end;
    private $record_id;

    public function __construct($start, $end, $record_id)
    {
        $this->start = $start;
        $this->end = $end;
        $this->record_id = $record_id;
    }

    public function __toString()
    {
        return sprintf("Record id:%d, diff: %d\n", $this->record_id, $this->diff());
    }

    public function diff()
    {
        return $this->start - $this->end;
    }

}

class AnalyzerFactory {
    public static function getInstance($type) {
        switch ($type) {
            case "football" :
                return new TeamAnalyzer(File::getInstance());
            case "weather":
                return new WeatherInfoAnalyzer(File::getInstance());
            default:
                throw new InvalidArgumentException("Invalid type in AnalyzerFactory");
        }
    }
}

$o = AnalyzerFactory::getInstance("football");
$o->prepareRecords();
echo $o->findRecordWithLeastDiff();

$o = AnalyzerFactory::getInstance("weather");
$o->prepareRecords();
echo $o->findRecordWithLeastDiff();

