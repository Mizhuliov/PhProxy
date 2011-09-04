<?PHP
/** 
 * Phproxy Client
 * 
 * PHP 5.3
 * 
 * @package   PhProxy_Client
 * @author    Alex Shcneider <alex.shcneider at gmail dot com>
 * @copyright 2010-2011 (c) Alex Shcneider
 * @license   license.txt
 * @link      http://github.com/Shcneider/PhProxy (sources, binares)
 * @link      http://vk.shcneider.in/forum (binares, support)
 * @link      http://alex.shcneider.in/ (author)
 **/

    /*
    100 Continue (����������).
    101 Switching Protocols (������������ ����������).
    102 Processing (��� ���������).

    200 OK (������).
    201 Created (�������).
    202 Accepted (�������).
    203 Non-Authoritative Information (���������� �� �����������).
    204 No Content (��� �����������).
    205 Reset Content (�������� ����������).
    206 Partial Content (��������� ����������).
    207 Multi-Status (��������������).
    226 IM Used (IM ������������).

    300 Multiple Choices (��������� �������).
    301 Moved Permanently (���������� ������������).
    302 Found (�������).
    303 See Other (�������� ������).
    304 Not Modified (�� ����������).
    305 Use Proxy (������������ ������).
    306 (���������������).
    307 Temporary Redirect (��������� ���������������).

    400 Bad Request (������ ������).
    401 Unauthorized (�������������).
    402 Payment Required (���������� ������).
    403 Forbidden (���������).
    404 Not Found (�� �������).
    405 Method Not Allowed (����� �� ��������������).
    406 Not Acceptable (�� ���������).
    407 Proxy Authentication Required (���������� �������������� ������).
    408 Request Timeout (����� �������� �������).
    409 Conflict (��������).
    410 Gone (�����).
    411 Length Required (���������� �����).
    412 Precondition Failed (������� ������).
    413 Request Entity Too Large (������ ������� ������� �����).
    414 Request-URI Too Long (������������� URI ������� �������).
    415 Unsupported Media Type (���������������� ��� ������).
    416 Requested Range Not Satisfiable (������������� �������� �� ��������).
    417 Expectation Failed (��������� �� ���������).
    418 I'm a teapot (� - ������).
    422 Unprocessable Entity (���������������� ���������).
    423 Locked (�������������).
    424 Failed Dependency (������������� �����������).
    425 Unordered Collection (��������������� �����).
    426 Upgrade Required (���������� ����������).
    449 Retry With (��������� �...).
    456 Unrecoverable Error (���������������� ������...).



    502 Bad Gateway (������ ����).
    503 Service Unavailable (������ ����������).

    505 HTTP Version Not Supported (������ HTTP �� ��������������).
    506 Variant Also Negotiates (������� ���� ����������).
    507 Insufficient Storage (������������ ���������).
    509 Bandwidth Limit Exceeded (��������� ���������� ������ ������).
    510 Not Extended (�� ���������).
     * 
     */


/**
 * PhProxy HTTP_Response Parser/Generator
 */
class PhProxy_HTTP_Response {
        
    // work mode (1 - parser, 0 - generator)
    private $_mode = 0;
    
    private $_version = '1.0';
    
    private $_code = 200;
    
    // raw body
    private $_raw_body = '';
    private $_replace = array();
    private $_replacement = array();
    
    // eol
    private $_eol = "\r\n";
    
    private $_headers = array();
    
        // last error
    private $_error_code = 0;
    private $_error = 'null';
    
    
    private $_codes = array(
        200 => 'OK',
        
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        408 => 'Request Timeout',
        
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        504 => 'Gateway Timeout',
        509 => 'Bandwidth Limit Exceeded'
    );
    
    
    // constructor
    public function __construct($code = 404)
    {
        // generator mode
        if (is_int($code)) {
            
            $this->_mode = 0;
            
            // set status code
            $this->_code = $code;
            
            // set server
            $this->header_add('Server', PhProxy::version());
            
            // set date
            $this->header_add('Date', $this->date());

            
        } else {
            
            // set raw data and work mode
            $this->_raw = $code;
            $this->_mode = 1;
            
            $this->_parse($this->_raw);
            
        }
        
        return true;
    }
    
    // destroy object
    public function destroy()
    {
        unset($this);
    }
    
    // return HTTP-valid time
    public function date($time = 0)
    {
        if ($time == 0) {
            $time = time();
        }

        return gmdate("D, d M Y H:i:s", $time)." GMT";
    }
    
    // add header (existed will be overwritten)
    public function header_add($name, $value)
    {
        return $this->_headers[$name] = $value;
    }
    
    // remove header
    public function header_rm($name)
    {
        if (!isset($this->_headers[$name])) {
            return false;
        }
        unset($this->_headers[$name]);
        return true;
    }
    
    // set body
    public function body_set($raw)
    {
        $this->_raw_body = $raw;
    }
    
    // set replace in body
    public function body_replace($it, $to)
    {
        $this->_replace[] = $it; $this->_replacement[] = $to;
    }
     
    // build
    public function build()
    {
        // compile body
        $this->_raw_body = str_replace($this->_replace, $this->_replacement, $this->_raw_body);
        
        // add content length in gen-mode
        if ($this->_mode == 0) {
            if (!isset($this->_headers['Content-Length'])) {
                 $this->header_add('Content-Length', strlen($this->_raw_body));
            }
        }
        
        // status line
        $return = 'HTTP/'.$this->_version.' '. $this->_code.' '. $this->_codes[$this->_code]. $this->_eol;

            // add all headers
            foreach ($this->_headers as $header => $value)
            {
                $return .= $header.': '.$value.$this->_eol;
            }

        // final eol
        $return .= $this->_eol;

        // add body
        $return .= $this->_raw_body;
        
        return $return;
    }
    
# -------------------------------------------------------- >> Private Methods
    
    // parsing raw request
    private function _parse($raw)
    {
        // set raw text and raw length
        $this->_raw = $raw;
        $this->_raw_len = strlen($this->_raw);

            // explode on headers and body
            if (strpos($this->_raw, $this->_eol.$this->_eol) === false) {
                $this->_error_code = 1; 
                $this->_error = 'HTTP request must have separator between body and headers!';
                return false;
            }

        // exploding
        list($this->_raw_head, $this->_raw_body) = @explode($this->_eol.$this->_eol, $this->_raw, 2);

        // parse head to method, path, proto, headers and etc.
        $ret = $this->_parse_head($this->_raw_head);
            if ($ret === false) {
                return false;
            }
        $this->_headers = $ret;
        
            // parse Host header
            if (!isset($this->_headers['Host'])) {
                $this->_error_code = 3; 
                $this->_error = 'Please, set "Host:" header!';
                return false;
            }
        
        $host = $this->_headers['Host'];
        
            // check port
            if (strpos($host, ':') !== false) {
                list($host, $port) = explode(':', $host, 2);
            } else {
                $port = 80;
            }
        
        // set host and port
        $this->_host = $host; $this->_port = $port;

        return true;    
    }
    
    // parse HTTP head 
    private function _parse_head($head)
    {       
        // parsing headers
        if (strpos($head, $this->_eol) === false) {
            $hh = array($head);
        } else {
            $hh = explode($this->_eol, $head);
        }
        
        // return array
        $ret = array(); 
        
        foreach ($hh as $num => $h)
        {
            if ($num == 0) { // first line
                
                $this->_raw_status_line = $h;
                preg_match('/(GET|POST|CONNECT|HEAD|OPTIONS)\s([^\s]+)\sHTTP\/([0-9\.]+)/i', $h, $arr);
                    if ($arr == false) { 
                        $this->_error_code = 2; 
                        $this->_error = 'Cannot parse first line of headers.';
                        PhProxy::event('Cannot parse first line of request: ['.$h.']');
                        return false;
                    }
                    
                // set request data
                $this->_method = $arr[1];
                $this->_uri = $arr[2];
                $this->_version = $arr[3];
                
                
                // uri to RFC
                if (strpos($this->_uri, 'http://') == 0) {
                    
                    
                    $tmp = explode('/', substr($this->_uri, 7), 2);
                        if (isset($tmp[1])) {
                            $this->_uri = '/'.$tmp[1];
                        } else {
                            $this->_uri = '/';
                        }
 
                    
                } elseif (strpos($this->_uri, 'https://') == 0) {
                    
                    $tmp = @explode('/', substr($this->_uri, 8), 2);
                        if (isset($tmp[1])) {
                            $this->_uri = '/'.$tmp[1];
                        } else {
                            $this->_uri = '/';
                        }
                    
                }
                
                
                
                continue;
                
            } else {
                
                // unkown format
                if (strpos($h, ": ") === false) {
                    PhProxy::event('HTTP request error parse: not found ":" on line '.$num);
                    continue;
                }
                
                // split
                list($name, $val) = explode(': ', $h, 2);
                $ret[trim($name)] = trim($val);  
            }  
        }
        
        return $ret;
    }   
    
}






?>