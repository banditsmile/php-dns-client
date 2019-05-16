<?php

require 'autoload.php';

/**
 * Class index
 */
class index{
    private $_param = array();

    /**
     * index constructor.
     */
    public function __construct($argv)
    {
        if (PHP_SAPI == 'cli') {
            $action = $argv[1];
            $this->_param = array_slice($argv, 2);
        } else {
            $this->_param = $_REQUEST;
            $action = $this->_get('a', 'query');
        }

        if (!method_exists($this, $action)) {
            $this->_out([], 3, '��Ϊ��������');
        }
        return $this->$action();
    }

    /**
     * ��ȡ����
     *
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    private function _get($key, $default=null)
    {
        if (isset($this->_param[$key])) {
            return $this->_param[$key];
        }
        return $default;
    }

    /**
     * ������
     *
     * @param array  $data
     * @param int    $code
     * @param string $msg
     *
     */
    private function _out($data=array(), $code=0, $msg='ok')
    {
        $ret=array(
            'code'=>$code,
            'msg' =>$msg,
            'data'=>$data
        );
        echo json_encode($ret);
        exit();
    }

    /**
     * ��¼��ѯ
     *
     * @param string $dns dns '223.5.5.5
     * @param string $domain ��ѯ����
     * @param string $type   ��ѯ����
     *
     * @return null
     */
    public function query()
    {
        $data = array('code'=>0, 'msg'=>'ok');
        $dnsServer = isset($_GET['dns']) ? $_GET['dns']:'223.5.5.5';
        if (!isset($_GET['domain'])) {
            $this->_out([], 1, '������������Ϊ��');
        }
        $domain = $_GET['domain'];
        $queryType = isset($_GET['type']) ? $_GET['type']:'A';

        try{
            $dns = new Metaregistrar\DNS\dnsProtocol();
            $dns->setServer($dnsServer);
            $result = $dns->Query($domain, $queryType);
        }catch (Exception $e){
            $this->_out([], 2, $e->getMessage());
        }

        /* @var $result Metaregistrar\DNS\dnsResponse */
        foreach ($result->getResourceResults() as $resource) {
            if ($resource instanceof Metaregistrar\DNS\dnsAresult) {
                $data[]=array(
                    'domain'=>$resource->getDomain(),
                    'ipv4'  =>$resource->getIpv4(),
                    'ttl'   =>$resource->getTtl()
                );
            }
        }
        $this->_out($data);
    }

    public function rootzone()
    {
        $zoneFile = 'https://www.internic.net/domain/root.zone';
        $ns = array();
        $rootzone = file($zoneFile, FILE_IGNORE_NEW_LINES);
        foreach ($rootzone as $zone) {
            $array = explode("\t", $zone);
            if (isset($array[2]) && $array[2]=='IN' && $array[3]=='NS') {
                $tld = strtolower($array[0]);
                if ($tld) {
                    $tld=trim($tld, '.');
                    $ns[$tld][] = $array[4];
                }
            }
        }
        $this->_out($ns);
    }
}
$instance = new index($argv);