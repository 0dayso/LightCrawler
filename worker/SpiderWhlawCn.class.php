<?php

/**
 * 炜衡律师事务所
 * http://www.whlaw.cn/Lawnews/index/p/2.html
 * User: Liang Tao (liangtaohy@163.com)
 * Date: 17/5/8
 * Time: PM3:45
 */
define("CRAWLER_NAME", "spider-www.whlaw.cn");
require_once dirname(__FILE__) . "/../includes/lightcrawler.inc.php";

class SpiderWhlawCn extends SpiderFrame
{
    const MAGIC = __CLASS__;

    /**
     * Seed Conf
     * @var array
     */
    static $SeedConf = array(
        "http://www.whlaw.cn/Lawnews/index/p/1.html",
    );

    protected $ContentHandlers = array(
        "#http://www\.whlaw\.cn/Lawnews/index/p/[0-9]+\.html# i" => "void",
        "#http://www\.whlaw\.cn/Index/Lawnews/detail/id/[0-9]+\.html# i"    => "handleDetailPage",
        "#/[0-9a-zA-Z_]+\.(doc|pdf|txt|xls)# i" => "handleAttachment",
    );

    /**
     * SpiderSjrShGov constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    // h3 class="news_title"
    protected function _handleDetailPage(PHPCrawlerDocumentInfo $DocInfo)
    {
        $source = $DocInfo->source;

        $extract = new ExtractContent($DocInfo->url, $DocInfo->url, $source);

        $doc = $extract->getExtractor()->extractor->document();
        $document = $extract->getExtractor()->extractor->domDocument();
        $title = $doc->query("//h3[@class='news_title']")->item(0)->nodeValue;

        $publish_time = $doc->query("//p[@class='newstime']")->item(0)->nodeValue;

        $zcontent = $document->saveHTML($doc->query("//div[@class='zcontent']")->item(0));

        $extract1 = new ExtractContent($DocInfo->url, $DocInfo->url, $zcontent);
        $extract1->parse();
        $content = $extract1->getContent();

        if (empty($content)) {
            $extract->parse();
            $content = $extract->getContent();
        }

        $c = preg_replace("/[\s\x{3000}]+/u", "", $content);
        $record = new XlegalLawContentRecord();
        $record->doc_id = md5($c);
        $record->title = $title;
        $record->author = "炜衡律师事务所";
        $record->content = $content;
        $record->doc_ori_no = $extract->doc_ori_no;
        $record->publish_time = !empty($publish_time) ? strtotime($publish_time) : $extract->publish_time;
        $record->t_valid = $extract->t_valid;
        $record->t_invalid = $extract->t_invalid;
        $record->negs = implode(",", $extract->negs);
        $record->tags = "律所实务";
        $record->simhash = '';
        if (!empty($extract->attachments)) {
            $record->attachment = json_encode($extract->attachments, JSON_UNESCAPED_UNICODE);
        }

        if (empty(gsettings()->debug)) {
            $res = FlaskRestClient::GetInstance()->simHash($c);

            $simhash = '';
            if (isset($res['simhash']) && !empty($res['simhash'])) {
                $simhash = $res['simhash'];
            }

            if (isset($res['repeated']) && !empty($res['repeated'])) {
                echo 'data repeated: ' . $DocInfo->url . ', repeated simhash: ' . $res['simhash1'] .PHP_EOL;
                $flag = 1;
                if (!empty($record->doc_ori_no)) {
                    $r = DaoXlegalLawContentRecord::getInstance()->ifDocOriExisted($record);
                    if (empty($r)) {
                        $flag = 0;
                    }
                }

                if ($flag)
                    return false;
            }

            $record->simhash = $simhash;
        }


        $record->type = DaoSpiderlLawBase::TYPE_TXT;
        $record->status = 1;
        $record->url = $extract->baseurl;
        $record->url_md5 = md5($extract->url);

        if (gsettings()->debug) {
            //echo "raw: " . implode("\n", $extract->text) . PHP_EOL;
            //$index_blocks = $extract->indexBlock($extract->text);
            //echo implode("\n", $index_blocks) . PHP_EOL;
            var_dump($record);
            exit(0);
        }
        echo "insert data: " . $record->doc_id . PHP_EOL;
        return $record;
    }
}