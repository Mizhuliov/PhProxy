<?PHP
// +-----------------------------------+
// | �������� ������...                |
// +-----------------------------------+

/*
 * ������ � ��������
 */
class PhProxy_Sockets {
    

    // ���������� ������ �������
    protected $sock_ready = false;

    // ���������� ������ �����
    protected $curl_ready = false;

    // ������ ������
    private $sock_error = null;

    // ������ ��������� ������
    private $socket = null;

    // ������� ��������
    public $sock_counter = 0;

    // ������ ���������� �����������
    public $sock_last = array();


    
    /*
     * ����������� ������ - �������� ������� ���������� (+)
     */
    protected function __construct()
    {
        if (function_exists('socket_create')) {
            $this->sock_ready = true;
        } if (function_exists('curl_init')) {
            $this->curl_ready = true;
        }
    }
    
    /*
     * ���������� ������ - �������� ������ (+)
     */
    protected function __destruct()
    {
        if ($this->socket) {
            $this->sock_close($this->socket);
        }
    }

    /*
     *  C������ ����� (+)
     */
    protected function sock_create($type = 'tcp')
    {
        if ($type == 'tcp') { // TCP
            $type = SOCK_STREAM; $protocol = SOL_TCP;
        } else { // UDP
            $type = SOCK_DGRAM;  $protocol = SOL_UDP;
        }

        $socket = @socket_create(_SOCK_DEFAULT_DOMAIN, $type, $protocol);
            if (!$socket) {
                $this->sock_error = @socket_strerror($socket); return false;
            }

        $this->socket = $socket;
        return $this->socket;
    }

    /*
     *  ���������� ������ (+)
     */
    protected function sock_gerror()
    {
        return $this->sock_error;
    }

    /*
     *  ��������� ����� (+)
     */
    protected function sock_close($socket = null)
    {
        if ($socket == null) {
            $socket = $this->socket;
        }
        return @socket_close($socket);
    }

    /*
     *  ������������� ����� ��� ������ (+)
     */
    protected function sock_no_block($socket = null)
    {
        if ($socket == null) {
            $socket = $this->socket;
        }
        $s = @socket_set_nonblock($socket);
            if (!$s) {
                $this->sock_error = @socket_strerror($s); return false;
            }
        return true;
    } 

    /*
     *  ������ ����� �� ������������ ����� - ���� (+)
     */
    protected function sock_bind($adr, $port, $socket = null)
    {
        if ($socket == null) {
            $socket = $this->socket;
        } 
        $bind = @socket_bind($socket, $adr, $port);
            if (!$bind) {
                $this->sock_error = @socket_strerror($bind); return false;
            }
        return true;
    }

    /*
     *  ������ ����� �� ������������� (+)
     */
    protected function sock_listen($bl, $socket = null)
    {
        if ($socket == null) {
            $socket = $this->socket;
        }
        $listen = @socket_listen($socket, $bl);
            if (!$listen) {
                $this->sock_error = @socket_strerror($listen); return false;
            }
        return true;
    }

    /*
     *  ��������� ���� �� �������� ����������  (+)
     */
    protected function sock_get($usleep, $socket = null)
    {
        if ($socket == null) {
            $socket = $this->socket;
        }

        while (true) {// ���� ��������� ����������
            $cnx = @socket_accept($socket);
                if ($cnx == false) {
                    usleep($usleep); continue;
                }
            break;
        }

        // ��������� ��������� ����������!
        $this->sock_counter++;

        // �������� ����:����
        $this->sock_last = $this->sock_get_name($cnx);
            return $cnx;
    }

    /*
     *  �������� port � ip (+)
     */
    protected function sock_get_name($cnx)
    {
        $adr = ''; $port = 0;
        $data = @socket_getpeername($cnx, $adr, $port);
        return array('ip'=>$adr, 'port'=>$port);
    }

   
    /*
     *  ������ � ������ (+)
     */
    protected function sock_read($cnx, $max = null)
    {
        // ������������ ������ ������ �������� � ������
        if (!$max) $max =  _SOCK_READ_STR_MAX_LEN;

        // ���� ����� ��, ��� ������ � ������
        $data = '';

        // ������ ������������� (������ �� ���������)
        $started = $this->microtime();

        // ������ � �����
        while (true) {
            $buf = @socket_read($cnx, $max, PHP_BINARY_READ);
                if ($buf === false) { // �� ������ - � �� ����
                    if ($started + _SOCK_READ_TIMEOUT < $this->microtime()) { // ��������� �������
                        $return = (strlen($data)) ? $data : false;
                        return $return;
                    }
                    if (strlen($data) && strpos($data, "\r\n\r\n") > 1) { // ���������� ���������
                        return $data;
                    }
                    usleep(_SOCK_READ_SLEEP);
                } elseif (is_string($buf) && strlen($buf) == 0) { // ���������� ���� �������
                    return -1;
                } else {
                    $data .= $buf;
                }
        }
        return false;
    }


    /*
     *  ����� � ����� (+)
     */
    public function sock_write($data, $socket = null)
    {
        if ($socket == null) {
            $socket = $this->socket;
        }

        $wr = @socket_write($socket, $data, strlen($data));
            if (!$wr) {
                $this->sock_error = @socket_strerror($wr); return false;
            }
        return true;
    }




}


?>