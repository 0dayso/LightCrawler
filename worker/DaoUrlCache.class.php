<?php

/**
 * Table urls
 * User: Liang Tao (liangtaohy@163.com)
 * Date: 17/4/18
 * Time: PM4:52
 */
class DaoUrlCache extends DaoBase
{
    const DB_NAME = 'spider';
    const TABLE_NAME = 'urls';

    const TYPE_HTML = 1;
    const TYPE_JSON = 2;
    const TYPE_TXT = 3;
    const TYPE_DOC = 4;
    const TYPE_DOCX = 5;
    const TYPE_XLS = 6;
    const TYPE_XLSX = 7;
    const TYPE_PDF  = 8;

    protected $_table_name = self::TABLE_NAME;
    /**
     * 表前缀
     * @var string
     */
    protected $_table_prefix = '';

    private static $inst;

    private $db;

    /**
     * @return DaoUrlCache
     */
    public static function getInstance()
    {
        if (empty(self::$inst)) {
            self::$inst = new self();
        }
        return self::$inst;
    }

    /**
     * DaoUrlCache constructor.
     */
    public function __construct()
    {
        $this->db = DBProxy::getInstance(self::DB_NAME);
    }

    /**
     * @param $spidername
     */
    public function cleanup($spidername)
    {
        $where = "spider='" . $spidername . "'";
        $this->db->delete($this->_table_prefix . $this->_table_name, $where);
    }

    /**
     * @param $spidername
     */
    public function pergecache($spidername)
    {
        $this->db->update("UPDATE urls SET in_process=0, processed=0 WHERE spider='{$spidername}'");
    }
}