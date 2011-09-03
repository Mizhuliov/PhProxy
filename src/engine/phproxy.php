<?PHP
// 
// +-----------------------------------------------------------------------------------+
// | PHP Version 5                                                                     |
// +-----------------------------------------------------------------------------------+
// | PhProxy 1.0 alpha - ������ ������ ��� ���������� �������� �� ���� � �� ������.    |
// +-----------------------------------------------------------------------------------+
// | Created by Alex Shcneider <alex.shcneider@gmail.com>(c) 2010                      |
// +-----------------------------------------------------------------------------------+
//

/*
 * ������� ���������...
 */
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush(1);

/*
 * ���������� ������� ����������� ��� ������������ �������, � ��������� ���������
 * � ����� �� ��������, ��� ������, ����, ������
 */
define('DS', DIRECTORY_SEPARATOR);
define('_ROOT', str_replace('engine', '', getcwd()).DS);
define("_LOGS", _ROOT.'logs'.DS);
define("_DATA", _ROOT.'data'.DS);

/*
 * ������������� ������������� ����������� ������, ��� ��� ���������� ����� �����������
 * ����� php-win.exe, ������� �� ����� STDOUT �, ��� ���������, ������������ �����������
 * �������� � ������������� �� ������������� Web-����������.
 */
ini_set('log_errors', true);
ini_set('log_errors_max_len', 1024);
ini_set('error_log', _LOGS.date('Y.m.d').'.txt');

    /*
     * ���� ������� ������� ���� ������.
     */
    function e($txt)
    {
        trigger_error($txt);
    }

/*
 * ���������� ���� � ����������� �������
 */
if (!file_exists(_ROOT.'engine'.DS.'config.php')) {
    e('���� � ����������� �� ������!'); exit();
}
require(_ROOT.'engine'.DS.'config.php');

// ������ �����������
if (!file_exists(_ROOT.'account.txt')) {
    e('���� � ������� ����������� �� ������!'); exit();
}
$data = @file_get_contents(_ROOT.'account.txt');
list($email, $pass) = @explode("\r\n", $data, 2);
$email = trim($email);
$pass = trim($pass);

// ������ �����������
define('_EMAIL',                $email);
define('_PASSWORD',             $pass);

/*
 * ����� ���������� ����� �������
 */
require(_ROOT.'engine'.DS. 'classes'.DS.'phproxy.sockets.class.php');
require(_ROOT.'engine'.DS. 'classes'.DS.'phproxy.http.class.php');
require(_ROOT.'engine'.DS. 'classes'.DS.'phproxy.client.class.php');
    if (!class_exists('PhProxy_Client')) {
        e('��������� ����� ���� �����������!'); exit();
    }

$proxy = new PhProxy_Client();

/*
 * �������������� ������������� ������
 */
$proxy->start_listing();

/*
 *  ����������� �� �������
 */
$proxy->auth_me(_EMAIL, _PASSWORD);

/*
 *  ������ � ����������� ���� ������������� ������ 
 */
do {
    // �������� ����� �������� ���������� (����� ���������� ����� �����������)
    $new_cnx    = $proxy->get_new_connection();
    
    // ������ ������ �� ��������� ���������� (����� ������� false)
    $data       = $proxy->read_from_socket($new_cnx);
        if (!$data) { // ���� ����� �� ������ ��� ��������� ������� (���������� �������)
            continue;
        }
        
    // ��������� ������, ������� �����
    $answer     = $proxy->parse_data($data);
   
    // ��������
    $proxy->write_to_socket($answer, $new_cnx);
        continue;

} while(true);


?>