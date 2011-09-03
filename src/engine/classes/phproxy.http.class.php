<?PHP
// +-----------------------------------+
// | �������� ������...                |
// +-----------------------------------+


class PhProxy_HTTP extends PhProxy_Sockets {

# -------------------------------------------------------------------> REQUEST
    /*
     * ������, ������� ���������� ����������
     */
    private $http_request_raw = NULL;


    /*
     * ������ ��������� � �������
     */
    private $http_request_raw_headers = null;

    /*
     * ������ ���� � �������
     */
    private $http_request_raw_body = null;

    /*
     * ������ ������ �������
     */
    private $http_request_start = null;

    /*
     * ������ �� ����� �����������
     */
    private $http_request_headers = array();

    /*
     * �����������, ��������� ���������� (+)
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     *  ���������� ������ (+)
     */
    public function __destruct()
    {
        parent::__destruct();
    }

     /*
      *  ������ ������, ��������� �� ������������ (+)
      */
    protected function http_request_parse($data)
    {
        // ���������� �������� ������
        $this->http_request_raw = $data; $arr = array();
        $this->http_request_start = null; $this->http_request_headers = array();

        // ��������� �� ����� � ����
        if (strpos($this->http_request_raw, "\r\n\r\n") === false) {
            $this->http_request_raw_headers = trim($this->http_request_raw);
            $this->http_request_raw_body = false;
        } else {
            $tmp = explode("\r\n\r\n", $this->http_request_raw, 2);
            $this->http_request_raw_headers = trim($tmp[0]);
            $this->http_request_raw_body    = trim($tmp[1]);
        }

        // �������� ��� ���������
        if (strpos($this->http_request_raw_headers, "\r\n") === false) {
            $arr[] = trim($this->http_request_raw_headers);
        } else {
            $arr = explode("\r\n", $this->http_request_raw_headers);
        }

        // ��������� ��������� �� ���: ��������
        foreach ($arr as $str)
        {
            // ������ ������
            if (strpos($str, 'GET') === 0 || strpos($str, 'POST') === 0 ) {
                $this->http_request_start = $str; continue;
            } elseif (strpos($str, ':') === false) {
                continue;
            } else {
                list($name, $val) = explode(':', $str, 2);
                $this->http_request_headers[trim($name)] = trim($val);
            }
        } 
        return true;
    }

    /*
     *  �������� ������ ����, ��������, ����������, ����� (+)
     */
    protected function http_request_check()
    {
        if (!$this->http_request_start || !$this->http_request_header_get('Host')) {
            return false;
        }

        // ��������� �� ���������
        $method = 'GET'; // method
        $host = $this->http_request_header_get('Host'); 
        $path = false; // ���� �� �����
        $ext = false; // ���������� �����

        @preg_match('/(GET|POST)\s([^\s]+)\sHTTP\/([0-9\.]+)/i', $this->http_request_start, $arr);
            if ($arr == false) { // ������ �������
                return false;
            }
            
        $method = $arr[1]; $uri = $arr[2];

        // ���� ���� - �������� http
        if (strpos($uri, 'http://') === 0) {
            $uri = preg_replace('/^http:\/\//', '', $uri, 1);
        }
        
        // ���������� �����/����
        if (strpos($uri, '/') === false) { // ���� ��� ����� - domain.com
            $host = trim($uri);
        } elseif(strpos($uri, '/') === 0) { // ���� ���� ������ - /docs/file.ext
            $path = substr($uri, 1);
        } else {
            list($host, $path) = explode('/', $uri, 2);
        }
       

        // ���������� ����������
        if ($path && strpos($path, '?') !== false) {
            list($d, $vars) = explode('?', $path, 2);
        } else {
            $d = $path;
        }
        if ($d && strpos($d, '.') !== false) {
            $a = explode('.', $d);
            $ext = $a[sizeof($a)-1];
        }

        return array(
            'method' => $method,
            'host' => $host,
            'path' => $path,
            'ext' => $ext
        ); 
    }

    /*
     *  ������� �������� ��������� (+)
     */
    protected function http_request_header_get($name)
    {
        if (isset($this->http_request_headers[$name])) {
            return $this->http_request_headers[$name];
        }
        return false;
    }

    /*
     *  ������� ��������� (+)
     */
    protected function http_request_header_remove($name)
    {
        if (isset($this->http_request_headers[$name])) {
            unset($this->http_request_headers[$name]);
        }
    }

    /*
     *  ���������/������������ ��������� (+)
     */
    protected function http_request_header_add($name, $value)
    {
        $this->http_request_headers[$name] = trim($value);
    }

    /*
     *  �������� ��������� ������� (+)
     */
    protected function http_request_headers_compile()
    {
        $answer = $this->http_request_start."\r\n";


        // ��������� ��� ���������
        foreach ($this->http_request_headers as $h => $v)
        {
            $answer .= $h.': '.$v."\r\n";
        }

        // ��������� ����
        $answer .= "\r\n".$this->http_request_raw_body;
            return $answer;
    }


# ---------------------------------------------------------------------------> RESPONSE
    /*
     * ��������� ������� ������
     */
    private $http_response_codes = array(
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        408 => 'Request Timeout',
        500 => 'Internal Server Error'
    );
    
    /*
     * ������� ������ ������
     */
    private $http_response_code = null;
    
    /*
     * ������ ���������
     */
    private $http_response_version = "1.1";

    /*
     * ��������� ������
     */
    private $http_response_headers = array();

    /*
     * ���� ������
     */
    private $http_response_body = '';


    
    /*
     *  ��������� ����� �����
     */
    protected function http_response_new($version = '1.1', $code = '200')
    {
        if (!isset($this->http_response_codes[$code])) {
            $code = 500;
        }
        $this->http_response_code = $code;
        $this->http_response_version = $version;
        $this->http_response_headers = array();
        $this->http_response_body = '';
    }

    /*
     *  �������� ��������� (+)
     */
    protected function http_response_header($header, $value)
    {
        $this->http_response_headers[$header] = $value;
    }

    /*
     *  �������� ���� ��������� (+)
     */
    public function http_response_body($body)
    {
        $this->http_response_body .= $body;
    }

    /*
     *  ���������� ������ (+)
     */
    public function http_response_compile()
    {
        // ������ ������
        $answer = 'HTTP/'.$this->http_response_version.' '.
                  $this->http_response_code.' '.
                  $this->http_response_codes[$this->http_response_code].
                  "\r\n";

        // ��������� ��� ���������
        foreach ($this->http_response_headers as $h => $v)
        {
            $answer .= $h.': '.$v."\r\n";
        }

        // ��������� �������-������
        $answer .= 'Content-Length: '.strlen($this->http_response_body)."\r\n";

        // ��������� ����
        $answer .= "\r\n".$this->http_response_body;
            return $answer;
    }
  
}
?>