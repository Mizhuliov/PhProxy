<?PHP
// +-----------------------------------+
// | �������� ������...                |
// +-----------------------------------+



/*
 * ��������� ����� �������
 */
final class PhProxy_Client extends PhProxy_HTTP {

    // ��������
    private $_name = null;

    // ������
    private $_version = null;

    // ���������� ������������
    private $user_perms = array();

    /*
     * �����������, ��������� ���������� (+)
     */
    public function __construct()
    {
        parent::__construct();

        // name && version
        $this->_name                = _NAME;
        $this->_version             = _VERSION;

        /*
         * ��������� ������� ���������� ��� ������ � ���� � ��������
         */
        if (!$this->sock_ready) {
            e('�� �������� ���������� ��� ������ � ��������!'); exit();
        } if (!$this->curl_ready) {
            e('�� �������� ���������� ��� ������ � curl!'); exit();
        }
    }

    /*
     *  ���������� ������ (+)
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this);
    }

     /*
     *  ���������� microtime(); (+)
     */
    protected function microtime($stamp = null)
    {
        if ($stamp == null) {
            $stamp = microtime();
        }
        list($s, $ms) = explode(' ', $stamp);
        (float)$result = (float)$s + (float)$ms;
        return $result;
    }

    /*
     * ��������� ������������� ������ (+)
     */
    public function start_listing()
    {
        // ������� �����
        $sock = $this->sock_create('tcp');
            if (!$sock) {
               e('������ ��� �������� ������: ['.$this->sock_gerror().']'); exit();
            }

        // ��������� ����� � ������������� �����
        if (!$this->sock_no_block()) {
            $this->sock_close($sock);
            e('������ ��� �������� ������ � ������������� �����: ['.$this->sock_gerror().']'); exit();
        }
   
        // ������ ��� �� �����:����
        if (!$this->sock_bind(_SOCK_LISTEN_IP, _SOCK_LISTEN_PORT)) {
            $this->sock_close($sock);
            e('������ ��� ����������� ������: ['.$this->sock_gerror().']'); exit();
        }
   
        // ��������� ������������� ������
        if (!$this->sock_listen(_SOCK_LISTING_BACKLOG)) {
            $this->sock_close($sock);
            e('������ ��� ������� ������������� ������: ['.$this->sock_gerror().']'); exit();
        }
        return true;
    }

     /*
     * ����������� ������������ (+)
     */
    public function auth_me($email, $password)
    {
        $post = "proxy_email=".base64_encode(_EMAIL).
                "&proxy_password=".base64_encode(_PASSWORD).
                "&proxy_version=".base64_encode(_VERSION_STAMP);

        // �������� ��������������!
        $answer = $this->send_to_server($post);
            if (!$arr = @base64_decode($answer)) { // ������ ��� ����������� ��������������� ������
                define('_AUTHERROR', "��� ������� �������������� ������ ��������� ������������ �����!".$answer);
                return false;
            } if (!$arr = @unserialize($arr)) {
                define('_AUTHERROR', "��� ������� �������������� ������ ��������� ������������ �����!".$answer);
                return false;
            } if (!is_array($arr)) {
                define('_AUTHERROR', "��� ������� �������������� ������ ��������� ������������ �����!".$answer);
                return false;
            }

        if (isset($arr['error'])) { // ��������� ���� �� ������
            define('_AUTHERROR', $arr['error']); return false;
        }

        // ���� �����������, ���������� ���������� � �����
        define('_AUTHKEY', $arr['authkey']); 
        $this->user_perms = $arr;
            return true;
    }

    /*
     * ���������� ����� �������� ���������� (+)
     */
    public function get_new_connection()
    {
        return $this->sock_get(_SOCK_LISTING_INTERVAL);
    }

     /*
     * ���������� ����������� �� ������ (+)
     */
    public function read_from_socket($cnx)
    {
        $data = $this->sock_read($cnx);
            if ($data == -1) { // -1 - ������ ��� ����������
                return false;
            } elseif (!$data) { // �����������, �� ������ �� �������
                // �������� HTML ��� ������ ��� ������
                $data = $this->return_some_error(408, array('{error}', 'Request Timeout checked!'));
                // ��������, � ��������� �����
                $this->write_to_socket($data, $cnx);
                    return false;
            }
        return $data;
    }

    /*
     * ������ ������ ������������ (+)
     */
    public function parse_data($data)
    {
        // ��������, ������ �� �����������
        if (defined('_AUTHERROR') && !defined('_AUTHKEY')) {
            $data = $this->return_some_error(
                    403,
                    array('{error}', _AUTHERROR)
                );
            return $data;
        }

        // ������ ������
        $this->http_request_parse($data);

        // �������� ������ ������ � �������
        $arr = $this->http_request_check();
            if ($arr == false || $arr['host'] == false) { // ������ �������
                $data = $this->return_some_error(400, array('{error}', 'Bad Request!'));
                return $data;
            }

        // cs = cs([0-9]+)\.vkontakte\.ru
        if (strpos($arr['host'], 'cs') === 0 && strpos($arr['host'], 'vkontakte.ru') !== false) {
            $host = 'cs';
        } else {
            $host = $arr['host'];
        }


        // ��������� ����������� ��������� � ������� �����
        $allow = $this->allow_request_to($host);
             if (!$allow) { // ������ � ����� ��������
                $data = $this->return_some_error(
                            403,
                            array('{error}', '��� ���������� ���������� � ����� <b>'.$host.'</b>!<br/>
                                              ��� ������� �� ��������������� ������ � ������ ������!')
                        );
                return $data;
             }

        // ��������� ����������
        if ($arr['ext'] && !$this->allow_request_ext($arr['ext'])) {
            $data = $this->return_some_error(
                            403,
                            array('{error}', '��� ���������� ��������� ����� ������ ����!')
                        );
                return $data;
        }

        // ������� ��������� Proxy-Connection:
        $this->http_request_header_remove('Proxy-Connection');

        // ������������ ��������� Connection:
        $this->http_request_header_add('Connection', 'close');

        // �������� ��������� �������
        $request = $this->http_request_headers_compile();

        # ---------------------------------------------------------------- >
        // ���������� ������ �� ������� ������
        $post = 'host='.$host.
                '&data='.base64_encode($request).
                '&authkey='._AUTHKEY.
                '&proxy_version='.base64_encode(_VERSION_STAMP);

            // �������� �����
            $answer = $this->send_to_server($post, $this->http_request_header_get('User-Agent'));
                if (!$answer) {
                    $data = $this->return_some_error(
                            500,
                            array('{error}', '������ ��� ��������� ������������� ��������!')
                        );
                return $data;
                }

        return $answer;
    }


    /*
     * ����� ����� � �����, ��������� ����� (+)
     */
    public function write_to_socket($str, $cnx)
    {
        // ��������, ��������� ����������
        $this->sock_write($str, $cnx);
        $this->sock_close($cnx);
    }

    /*
     * ���������� ����������� �������� � ������� (+)
     */
    protected function return_some_error($code, $error)
    {
        $this->http_response_new('1.1', $code);
        $this->http_response_header('Connection', 'close');
        $this->http_response_header('Content-type', 'text/html; charset=windows-1251');

        // ������ �������� � ������� ��� ����� ����
        if (file_exists(_DATA.'errors'.DS.$code.'.txt')) {
            $txt = file_get_contents(_DATA.'errors'.DS.$code.'.txt');
            $txt = str_replace($error[0], $error[1], $txt);
        } else {
            $txt = "<h2>".$error."</h2>";
        }

        $this->http_response_body($txt);
        $data = $this->http_response_compile();
        return $data;
    }


    /*
     * ��������� ����������� ��������� � ����� (+)
     */
    private function allow_request_to($host)
    {
        if ($this->user_perms['hosts_policity'] == 'deny') { // ��� ���� �� ����������� - �� ����������
            if (!isset($this->user_perms['a_hosts'][$host])) {
                return false;
            }
        } else { // ��� ���� �� ���������� - �� ����������
            if (isset($this->user_perms['d_hosts'][$host])) {
                return false;
            }
        }
        return true;
    }

    /*
     * ��������� ����������� ������� � ����� ������ ���� (+)
     */
    private function allow_request_ext($ext)
    {
        if ($this->user_perms['exts_policity'] == 'deny') { // ��� ���� �� ����������� - �� ����������
            if (!isset($this->user_perms['a_exts'][$ext])) {
                return false;
            }
        } else { // ��� ���� �� ���������� - �� ����������
            if (isset($this->user_perms['d_exts'][$ext])) {
                return false;
            }
        }
        return true;
    }

    /*
     *  ���������� ������ �� ������ (+)
     */
    private function send_to_server($post, $ua = 'MSIE 10.0')
    {
        $ch = curl_init();
            if (!$ch) {
                e('������ ������������� CURL.'); exit();
            }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER,    1);
        curl_setopt($ch, CURLOPT_TIMEOUT,           _CURL_TIMEOUT);
        curl_setopt($ch, CURLOPT_USERAGENT,         $ua);
        curl_setopt($ch, CURLOPT_URL,               _CURL_GATEWAY);
        curl_setopt($ch, CURLOPT_POST,              1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,        $post);
        curl_setopt($ch, CURLOPT_HEADER,            0);
        curl_setopt($ch, CURLOPT_REFERER,           _CURL_REFERER);

        // ���������� �������
        $body = curl_exec($ch);

            // ��������� �� ������
            if (curl_errno($ch) != 0 && curl_error($ch)) {
                e('������ CURL - '.curl_error($ch)); return false;
            }

        // ��������� cURL ������
        curl_close($ch);
       
        return $body;
    }



}
?>