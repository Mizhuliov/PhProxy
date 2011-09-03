<?PHP #file_get_contents("res:///PHP/icon.ico");
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
 * @version   2.0.5 alpha2
 * @link      http://shcneider.in/forum
 **/

// ��������� �������
define('_DEBUG', 1);

# ---------------------------------------------------------- > ������ ��� PHP 4.4.4

if (version_compare(PHP_VERSION, '4.4.4')) {
    exit('For PHP 4.4.4 only!');
}

# ---------------------------------------------------------- > ��������� ������ ��� ������

// ������ ����������
define('APP_NAME',          'PhProxy');
define('APP_jVERSION',      '2');
define('APP_nVERSION',      '0');
define('APP_nBUILD',        5);
define('APP_sBUILD',        'Alpha2');
define('APP_dBUILD',        '11.04.2011');

// ���� � ����������� ��� ������
define('DS',                DIRECTORY_SEPARATOR);
define('APP_ROOT',          realpath('.'.DS) . DS);
define('APP_ROOT_LOGS',     APP_ROOT . 'logs' . DS); // 2.0.5

// ���� � ����������� ����������
define('CT_APP_ROOT',       '');
define('CT_APP_ROOT_SRC',   CT_APP_ROOT . 'include' . DS);
define('CT_APP_ROOT_IMGS',  CT_APP_ROOT . 'srcs' . DS);

// ���� � ����������� ���������� ( >= 2.0.5)
define('COMPILE_PATH',           '');
define('COMPILE_PATH_INCLUDE',   COMPILE_PATH . 'include' . DS);
define('COMPILE_PATH_IMGS',      COMPILE_PATH . 'imgs' . DS);


# ---------------------------------------------------------- > ����������� �������������

error_reporting(E_ALL | E_NOTICE);
ob_implicit_flush(1);
set_time_limit(0);

ini_set('display_errors',           True);
ini_set('register_argc_argv',       True);
ini_set('log_errors',               True);
ini_set('log_errors_max_len',       1024);
ini_set('error_log',                APP_ROOT_LOGS. 'Log_'.date('Y-m-d').'.log.txt');

# ---------------------------------------------------------- > ����������� ����������� ������

// ����� ��������� ���� (2.0.5 ++)
require COMPILE_PATH_INCLUDE . 'config.default.php';
require COMPILE_PATH_INCLUDE . 'defines.php';
require COMPILE_PATH_INCLUDE . 'functions.php';

# ---------------------------------------------------------- > �������������

// ��������� ����������� ������ � �������� ����� (2.0.5 ++)
$tname = APP_ROOT . md5(microtime());
$fp = @fopen($tname, 'w+');
    if (!$fp) {
        error_fatal(APP_NAME.' �� ����� ������ � �������� ����������� ['.APP_ROOT."]\r\n����������, ��������� �������� ������ �� ������!");
    }
@fclose($fp);
unlink($tname);


// ��������� ������������� ���-�����, ���� ��� - ������� (2.0.5 ++)
if (!@file_exists(APP_ROOT_LOGS)) {
    if (!@mkdir(APP_ROOT_LOGS, 0777)) {
        error_fatal('�� ������� ������� ����� ��� �������� ���-������: ['.APP_ROOT_LOGS.']');
    }
}

// ������ ��������� ���� (2.0.5 ++)
define('M_WIN_TITLE', version(cfg_get('gui_mainwin_title')));


    // ������ �� �������� ������� (2.0.5 ++)
    if (wb_get_instance(M_WIN_TITLE, true)) {
        event('---------------------->> ����������� ������� ���������� ������� ����������! <<-----------------------'); die();
    }

    
# ---------------------------------------------------------- > �������������

// �������� �������������
event();
event('������� ������� ���������...');
    if (_DEBUG) { // ������ ���������� ��� �������
        event(' -- ������ PhProxy: ['.version().']');
        event(' -- ������ PHP: ['.PHP_VERSION.']');
        event(' -- ������ ��: ['.@php_uname().']');
        event(' -- ���� �������: ['.APP_ROOT.']');
    }

    // ��������� ���������� �� ���������� WinBindera
    if (!extension_loaded('winbinder')) {
        error_fatal('���������� ��� ������ � WinBinder �� ��������!');
    } elseif(_DEBUG) {
        event('���������� ��� ������ � WinBinder �������� � ���������� �������!');
    }

    // ���������� ����������� ����� WB
    if (!include COMPILE_PATH_INCLUDE . 'winbinder' . DS . 'winbinder.php') {
        error_fatal('������ ��� �������� ��������� WB ������!');
    } elseif(_DEBUG) {
        event('��������� WB ����� ���������� �������!');
    }

    
// ���������� GUI ����� � ����� ������� ������ ���������� (����� ��������� ���� (2.0.5 ++))
require COMPILE_PATH_INCLUDE . 'classes' . DS . 'wb_window.class.php';
require COMPILE_PATH_INCLUDE . 'classes' . DS . 'application.class.php';


    // ��������� ������� ������������ ��� ������ GUI
    if (!class_exists('WB_Window')) {
        error_fatal('�� ������ ����� GUI � ��������� ����������!');
    } elseif(_DEBUG) {
        event('����� GUI ������� ���������!');
    }

event('�������������: ������� �����������!');

# ---------------------------------------------------------- > �������!

// ������� ��������� ������ ������� �������
$phproxy = new PhProxy_Net();

// ������� ������ �������� ����
event('������ �������� GUI ����������...');


    // ��������� ������������� ����������� ������ (2.0.5+)
    if (!file_exists(cfg_get('gui_mainwin_icon'))) {
        error_fatal('�� ������� ����� ����������� ��� ������ ����: ['.cfg_get('gui_mainwin_icon').']');
    } if (!file_exists(cfg_get('gui_mainwin_logo'))) {
        error_fatal('�� ������� ����� ����������� ��� ������ ����: ['.cfg_get('gui_mainwin_logo').']');
    }

$w_main = new WB_Window(0, AppWindow, WBC_TASKBAR | WBC_INVISIBLE);
    $w_main->title(M_WIN_TITLE);
    $w_main->position(WBC_CENTER, WBC_CENTER);
    $w_main->size(cfg_get('gui_mainwin_width'), cfg_get('gui_mainwin_height'));
    $w_main->icon(cfg_get('gui_mainwin_icon'));
$w_main->build();

event('���������� ������� ���� ����������!');


// ������� ������� ����
/*$mainmenu = wb_create_control($w_main->wobj, Menu, array(
   "&����",
       array(10,     "&New\tCtrl+N",     "", cfg_get('gui_mainwin_icon'),  "Ctrl+N"),
   "&������",
       array(11,    "&Help topics\tF1", "", null,  "F1")
)); */


// ��������� ������� � ����� ���� ��������
wb_set_image(
    wb_create_control($w_main->wobj, Frame, "", 0, 0, 600, 100, ID_LOGO, WBC_IMAGE),
    cfg_get('gui_mainwin_logo')
);


// ������������� ������
wb_create_control($w_main->wobj, Frame, L_CFSERVER, 5,   105, 170, 80, ID_FRAME_SERVER);
wb_create_control($w_main->wobj, Frame, L_CFAUTH,   180, 105, 310, 80, ID_FRAME_AUTH);
#wb_create_control($w_main->wobj, Frame, L_CSTATE,   5,   185, 245, 80, ID_FRAME_STATE);


// ������ ���������� ��������
$w_main->serverButtonStart = wb_create_control(
    $w_main->wobj, PushButton, array(L_BSTART, L_BSTART_DES), 15, 125, 150, 20, ID_SERVER_BSTART
);
$w_main->serverButtonStop  = wb_create_control(
    $w_main->wobj, PushButton, array(L_BSTOP, L_BSTOP_DES), 15, 150, 150, 20, ID_SERVER_BSTOP
);

// ��������� ������ ���������� ������� (���������� ������ �� ��������� :))
wb_set_enabled($w_main->serverButtonStop, 0);


// ���� �����������
// ������� � ����� �����
wb_create_control($w_main->wobj, Label,   L_LAUTH_EMAIL,   190, 127, 50,  15, ID_LAUTH_EMAIL);
wb_create_control($w_main->wobj, Label,   L_LAUTH_PASS,    190, 152, 50,  15, ID_LAUTH_PASS);


// ���� �����
$w_main->serverButtinAuthEmail = wb_create_control(
    $w_main->wobj, EditBox, 'guest@guest', 240, 125, 245, 20, ID_IAUTH_EMAIL
);
$w_main->serverButtinAuthPass  = wb_create_control(
    $w_main->wobj, EditBox, 'guest',       240, 150, 120, 20, ID_IAUTH_PASS, WBC_MASKED
);

// ������ ����������� � �����������
$w_main->serverButtonRegDo  = wb_create_control(
    $w_main->wobj, PushButton, L_BAUTH_REGDO,  365, 150, 120, 20, ID_BAUTH_REGDO
);

// ��c������ ���� ����� ���� � ������, � ��� �� ������ ����������� - � ����������
wb_set_enabled($w_main->serverButtinAuthEmail, 0);
wb_set_enabled($w_main->serverButtinAuthPass, 0);
wb_set_enabled($w_main->serverButtonRegDo, 0);


// ������� ��������� � ������������� ���� ������ ���������
$w_main->statusbar = wb_create_control($w_main->wobj, StatusBar, ' ');
$w_main->status(version());


event('GUI ���� ������� ��������...');

// �������� ��������� ������� ��� ����
wb_set_handler($w_main->wobj, 'phproxy_eventsHandler');

// ��������� ���������� ������
$phproxy->client_timer = wb_create_timer($w_main->wobj, ID_CLIENT_TIMER, cfg_get('net_client_timer'));

// ���������� ���� � ������ � ����
$w_main->visible(1); $w_main->loop();


    // ���������� ������� � GUI ����
    function phproxy_eventsHandler($win, $id, $con = 0, $param1 = 0, $param2 = 0)
    {
        global $phproxy;

            switch ($id) {

                // ������ ������� �������� ������
                case IDCLOSE:
                    phproxy_stop(); break;

                // ������ ������� �������
                case ID_SERVER_BSTART:
                    phproxy_server_start(); break;

                // ������ ��������� �������
                case ID_SERVER_BSTOP:
                    phproxy_server_stop(); break;

                // �������� ������ ��������� ������
                case ID_SOCKET_TIMER:
                    $phproxy->server_main(); break;

                // �������� ������ ���������� �����
                case ID_CLIENT_TIMER:
                    $phproxy->client_main(); break;

            }

        return true;
    }


?>