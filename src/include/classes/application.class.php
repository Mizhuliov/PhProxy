<?PHP
/**
 * PhProxy_Client - HTTP-������ ������ �� PHP ��� Win32 �������.
 *
 * PHP 4 && BamCompiler 1.21
 *
 * @package   PhProxy_Client
 * @category  PhProxy
 * @author    Alex Shcneider <alex.shcneider@gmail.com>
 * @copyright 2009-2011 (c) Alex Shcneider
 * @license   Lisense.txt
 * @version   {$VERSION}
 * @link      http://shcneider.in/forum
 **/



// ����� ���������� (������ PhProxy)
class PhProxy_Net {


# --------------------------------------- >
// ������ ����� ��������� �����������
var $auth_state  = 0;
var $auth_email  = 'guest@guest';
var $auth_pass   = 'guest';
var $auth_key    = 'no';
var $auth_expire = 0;


# --------------------------------------- >
// ����� ��������� ������ ��������� �����
var $error = null;

// ������ ������
var $socket = null;

// ������ ������
var $socket_timer = null;

// ��������� ���������� ������
var $server_state = 0;

// ������ ��������� ����������� �� ����� �������
var $server_cnx = 0;

// ����� ������
var $server_rbuffer = '';

// ����� ������
var $serve_answer = '';

// ����������� (������� ������)
var $server_connected = 0;



# --------------------------------------- >

// ���������� ������
var $client_timer = 0;

// ��������� ���������� �����
var $client_state = 0;

// ������� ������
var $client_connected = 0;

// ������ ���������� ����������
var $client_cnx = 0;

// ������ �� ��������
var $client_query = '';

// ���������� �����
var $client_answer = '';






    // ��������� ������ ������
    function serror($r = null)
    {
        $code = @socket_last_error($r);
            if (!$code) {
                return '{����������� ������ ������}';
            }

        return '{'.$code.' - '.socket_strerror($code).'}';
    }


    // ������� ������� �������
    function server_start()
    {
        global $w_main;

        // ���������� ����� ��������� ������
        $this->error = '����������� ������';
       
        // ��� ������ ����� �� �����
        if ($this->socket) {
            $this->error = '�������� ������ ���������� - ����� ��� ������!';
            return false;
        }

            // ���������� �� ���������� ��� ������ � ��������
            if (!extension_loaded('sockets')) {
                $this->error = '���������� ��� ������ � �������� �� ��������. ������ ������� �� ��������!';
                return false;
            }

        // ������ ��� �� ������ ������
        if (!function_exists('socket_create')) {
            $this->error = '������� ��� ������ � �������� �� ��������. ������ ������� �� ��������!';
            return false;
        }

            // �������� ��� TCP
            if (!defined('SOL_TCP')) { // ��������� ����...
                event('��������� ��� TCP ������ �� �����������...');
                // ���������� ����� ��������� ��� TCP
                $proto = @getprotobyname("TCP");
                    if (!$proto || $proto == -1) {
                        $this->error = '�� �� ��������, �� � ����� ��������� ��� ��������� TCP/IP!';
                        return false;
                    }

                // ���� ��������� ���������
                define('SOL_TCP', $proto);
                event('������� ������������� ��������� ��� TCP!');
            }

        // ������� �����
        $this->socket = $sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if (!$sock) {
                $this->error = '��� �������� ������: '.$this->serror($sock);
                return false;
            } else {
                event(' - ����� ������ AF_INET ���� SOCK_STREAM ��������� TCP ������� ������!');
            }

        // ��������� ����� � �������
        $res = @socket_bind($sock, cfg_get('net_server_ip'), cfg_get('net_server_port'));
            if (!$res) {
                $this->error = '��� ����������� ������: '.$this->serror($sock);
                return false;
            } else {
                event(' - ��������� ����� ������� ��������� �� �����:���� - '.cfg_get('net_server_ip').':'.cfg_get('net_server_port'));
            }

        // ��������� ����� � ������������� �����
        socket_set_nonblock($sock);

        // ������ ����� �� �������������
        $res = socket_listen($sock, cfg_get('net_server_backlog'));
            if (!$res) {
                $this->error = '��� ��������� ������ �� �������������: '.$this->serror($sock);
                return false;
            } else {
                event(' - ����� ������� ���������� �� ������������� � ������������ ������� �������: '.cfg_get('net_server_backlog'));
            }

        // ����� ������� ���������� �� �������������
        return true;
    }


    // ������� ��������� �������
    function server_stop()
    {
        global $w_main;
            if (!$this->socket) {
                $this->error = '������ �� �������. ���������� ����������!';
                return false;
            }

        event(' - ����������� ������� ������');
        wb_destroy_timer($w_main->wobj, $this->socket_timer);

        event(' - �������� ������');
        socket_close($this->socket);
        $this->socket = null;

        return true;
    }


    // ������� ������ ������� ���������� �� �������
    function server_main()
    {
        // ��������� ������ 0 - �� �����������
        if ($this->server_state == 0) {
            $this->server_cnx = @socket_accept($this->socket);
                if ($this->server_cnx) { // ����� ���������� ���������
                    socket_set_nonblock($this->server_cnx);
                    $this->server_state = 1;
                    $this->server_connected = timer()+cfg_get('net_server_read_timeout'); // ���������� ����� �����������
                } else {
                    return true;
                }
        }

        
        // ��������� ������ 1 - �����������, ���������� ���������
        if ($this->server_state == 1) {

                // ��������� ������� ������
                if ($this->server_connected < timer()) {
                    $this->server_answer = 'HTTP/1.0 408 Request Timeout'."\r\n\r\nRequest Timeout";
                    $this->server_state = 5;
                    return true;
                }

            // ������� ���������
            $data = @socket_read($this->server_cnx, cfg_get('net_server_read_buffer'), PHP_BINARY_READ);
                if (is_string($data)) { // ������� ��� �� ���������
                    if (strlen($data) == 0) { // ���������� ���� �������
                        $this->server_state = 6;
                    } else {
                        $this->server_rbuffer .= $data;
                        if (strlen($data) < cfg_get('net_server_read_buffer') && strpos($this->server_rbuffer, "\r\n\r\n") !== false) {
                            $this->server_state = 2;
                        }
                    }
                    
                } else { // ��������� ������ ������ == false
                    if (strlen($this->server_rbuffer)) {
                        $this->server_state = 2;
                    } else {
                        $this->server_state = 6;
                    }
                }
               
        }


        // ��������� ������ 2 - ������ ��������, ����� ��� ����������
        if ($this->server_state == 2) {

            // ������������ ���������
            list($h, $b) = explode("\r\n\r\n", $this->server_rbuffer, 2);
                if ($h === false or $b === false) {
                    $this->server_answer = '�� ������� ���������� ������!';
                    $this->server_state = 5;
                    return true;
                }

            $hh = @explode("\r\n", $h);
                if (!is_array($hh)) {
                     $this->server_answer = '�� ������� ���������� ������!';
                    $this->server_state = 5;
                    return true;
                }

            $nh = '';
                // ���������� ���
                foreach ($hh as $h) {
                    if (strpos($h, 'Proxy-Connection: ') === 0) {
                        
                    } elseif (strpos($h, 'Connection: ') === 0) {

                    } else {
                        $nh .= $h."\r\n";
                    }
                }

            $nh .= 'Connection: close'."\r\n";
            $nh .= "\r\n".$b;

            $this->server_rbuffer = $nh;

            $this->server_state = 3;
        }

        
        // ��������� ������ 3 - ������ ���������, ����� ��������� �� ��������� ������
        if ($this->server_state == 3) {

                // ���� ���������� ����� ��� �� ������ ��������� ����������
                if ($this->client_state != 0) {
                    return false;
                }

            // �������� � ���������� ����� ��� ������ � ������ ������
            $this->client_query = $this->client_query_encode('getURL', $this->server_rbuffer);
            $this->client_state = 1;

            // � ���� ���� ������ �� ���������� �����
            $this->server_state =  4;
        }


        // ��������� ������ 4 - ���� ������ � ������� � ������ �� ������


        // ��������� ������ 5 - ����� �������,����� ��������
        if ($this->server_state == 5) {

            // ������� ��������� ������ ������� (2.0.5+)
            if (strpos($this->server_answer, "\r\n\r\n") !== false) {
                list($temp, $ans) = @explode("\r\n\r\n", $this->server_answer, 2);
            } else {
                $ans = $this->server_answer;
            }

            // ��������
            @socket_write($this->server_cnx, $ans, strlen($ans));
            $this->server_state =  6;
        }


        // ��������� ������ 6 - ����� ������� �����, � �������� ��� ����������
        if ($this->server_state == 6) {
            socket_close($this->server_cnx);
            $this->server_rbuffer = '';
            $this->server_state = 0;
        }

    }

// ----------------------------------------------------------- >> ���������� �������

    // ������� ������ ������� ���������� �� �������
    function client_main()
    {
        // ��������� 0 - ������ ������ �� ����� - return
        if ($this->client_state == 0) {
            return true;
        }
        
        // ��������� 1 - ����� ��������� � ��������
        if ($this->client_state == 1) {
            // �������� ���������� ����������
            $this->client_cnx = $this->client_open(cfg_get('net_remote_domain'), cfg_get('net_remote_port'), cfg_get('net_remote_timeout'));
                if (!$this->client_cnx) { // �� �������
                    $this->client_state = 4;
                    $this->client_answer = '�� ������� ��������� � ������-��������!';
                } else {
                    $this->client_state = 2;
                }
        }

        
        // ��������� 2 - ����� �������� � ����� ������
        if ($this->client_state == 2) {
            $res = $this->client_send_post($this->client_cnx, $this->client_query);
                if (!$res) {
                    $this->client_state = 4;
                    $this->client_answer = '�� ������� ��������� � ������-��������!';
                } else {
                   $this->client_state = 3;
                   return true;
                }
        }


        // ��������� 3 - ����� ��������� �����
        if ($this->client_state == 3) {
            // ������
            $data = $this->client_read($this->client_cnx);
                if ($data) {
                    $this->client_answer .= $data; return true;
                } else {
                    if (strlen($this->client_answer)) {
                         $this->client_state = 4;
                    } else {
                        return true;
                    }
                }
        }


        // ��������� 4 - ������ ����� ��������� �����, �������� ���
        if ($this->client_state == 4) {
            $this->server_answer = $this->client_answer;
            $this->server_state = 5;

            // ���������� ��� ���������
            $this->client_answer = $this->client_query = '';

            $this->client_close($this->client_cnx);
            $this->client_state = $this->client_cnx = 0;
        }


    }


    // �������� ����������� ���������� � �������
    function client_open($host, $port, $timeout)
    {
        // ��������� ������
        $errno = $errstr = '';

        // �������� ��������� ����� �� �������
        $d = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if (!$d) {
                event('�� ������� ��������� � �������� �� '.$timeout.' ������ ['.$errstr.']');
                return false;
            }
        stream_set_blocking($d, 0);

        return $d;
    }


    // �������� ������ � ���������� ����������
    function client_send_post($cnx, $data)
    {
        // ������ ���� ������
        $req = 'POST '.cfg_get('net_remote_path').' HTTP/1.1'."\r\n".
        'Host: '.cfg_get('net_remote_domain')."\r\n".
        'User-Agent: '.version('%an%/%avj%.%avn%.%avb% %avs%')."\r\n".
        'Referer: '.cfg_get('net_remote_referer')."\r\n".
        'Connection: close'."\r\n".
        'Content-Length: '.strlen($data)."\r\n".
        'Content-Type: application/x-www-form-urlencoded'."\r\n".
        "\r\n".
        $data;


        // ���������� ������ �� ������
        $send = @fwrite($cnx, $req, strlen($req));
            if (!$send) {
                event('�� ������� �������� ������ � �����!'); return false;
            }

        return $send;
    }

    
    // ������ ������ � ����������
    function client_read($cnx)
    {
        if (feof($cnx)) {
            return false;
        }
        $data = fread($cnx, cfg_get('net_remote_read_buff'));
            if (!$data) {
                return false;
            }

        return $data;
    }


    // �������� ����������� ����������
    function client_close($cnx)
    {
        return @fclose($cnx);
    }


    // �������� ������ ��� ��������
    function client_query_encode($act = 'getURL', $data = null)
    {
        // �������� � ������ �������
        $query = 'version='.version('%avb%').'&';
        $query .= 'authkey='.$this->auth_key.'&';

            // ���� ���������� ������ getURL - ��������� ���������� ���������
            if ($act == 'getURL') {
                $query .= 'act=getURL&';
                $query .= 'data='.urlencode(base64_encode($data));
            }

        return $query;
    }


/*
// ������ �����������
var $auth_timer = 0;

// �������� �����������
var $auth_stage = 0;
var $auth_sub_stage = 1;

// ������ ��������� ���������� �����������
var $auth_cnx = 0;

// ������ ����������� �� �������
var $auth_data = '';

# --------------------------------------- >

// ������ ����� ��������� �������
var $server_state = 0;

// ����� ������� �������
var $server_started = 0;

// ����������� �������� ���������� � ������� �������
var $server_incoming = 0;

# --------------------------------------- >

// ������ ������ Tahoma 8pt Bold
var $gui_bold_font = 0;

// ������� ����������
var $gui_server_state_ctrl = 0;
var $gui_server_uptime_state_ctrl = 0;
var $gui_server_incom_state_ctrl = 0;

#var $gui_uptime_state_ctrl = 0;

var $gui_auth_state_ctrl = 0;

# --------------------------------------- >

// ��������� ������
var $system_timer = 0;

// ����� �������
var $started = 0;

# --------------------------------------- > HTTP

// ������ ����� �������
function html_parse_response($data)
{
    // ���� ����������� ��������� � ����
    if (strpos($data, "\r\n\r\n") === false) {
        event('������ ��� �������� ������ - ��� ���������� ���������-����!');
        return false;
    }

    // �����
    list($headers, $body) = @explode("\r\n\r\n", $data, 2);

    // ������ ����
    if (empty($body)) {
        event('��������� ������ ���� ������!'); return false;
    }

    // ��������� ���������
        if (strpos($body, '&') === false) {
            $params = array($body);
        } else {
            $params = @explode('&', $body);
        }

    // ��������� �� ����=��������
    $c = sizeof($params); $return = array();

    // ���������� ���� ������
    for ($i=0; $i<$c; $i++) {
        if (strpos($params[$i], '=') !== false) {
            list($key, $value) = @explode('=', $params[$i]);
            $return[$key] = $value;
        }
    }

    // ��������� ������������ ���� �����
    if (!isset($return['state'])) {
        event('��� ������������� ��������� state � ������...'); return false;
    }

    return $return;
}

// ����� ��������� ������
var $error = null;

// �������������� �����
var $socket = null;

// ������ �������
var $stimer = null;

# --------------------------------------- >

// ������ �������� ����������
var $cnx = null;

// ���� ��������� �������� ����������
var $state = 0;

// ������ ����������� � ������
var $srequest = '';

// ������ ��� ��������
var $sresponse = '';

// �������� �����
var $fs = null;

# --------------------------------------- >



*/

}


/*

    /* // ���������� Program Files
        if (isset($_SERVER["ProgramFiles"])) {
            $pf = strtolower($_SERVER["ProgramFiles"].DS);
        } else {
            fatal('������ ����������� ������� �����������...');
        }
    // �� �������� ��������� ��������� ��������, ����� ����� program files
        if (strtolower(APP_ROOT) != $pf . 'phproxy' . DS && strtolower(APP_ROOT) != 'e:\\phproxy\\home\\') {
            fatal('��������� ������������ �������...'."\r\n".'����������, �������������� ���������!');
        }
    // ���������� �������� ������� ��� ������
        if (!isset($_SERVER["APPDATA"])) {
            fatal('������ ����������� ����������� ��� �������� ������...');
        }
    // ��������� ���� �� ����� � ������� ���������
        if (!file_exists($_SERVER["APPDATA"] . DS . 'PhProxy')) { // ����� ���� - ������ ��� ������ ������
            #$succ = @mkdir($_SERVER["APPDATA"] . DS . 'PhProxy');
        } */





/*

// ��������� ������� �� ������� - �������� ���� � ���������
function w_close($w) {
    global $w_main;

        if (wb_message_box($w_main, WM_TXT_CLOSEW_TEXT, WM_TXT_CLOSEW_TITLE, WBC_YESNO)) {
            wb_destroy_window($w);
        }

    return true;
}




// ��������� ��������� ��������
pclose(popen('start "PhProxy" "'.RT_ROOT_EXE . 'php-win.exe" '. RT_ROOT_SCRIPTS . 'phproxy.php '.$action, 'r'));

*/


?>