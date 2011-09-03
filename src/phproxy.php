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
 * @version   2.0.4 alpha1
 * @link      http://shcneider.in/forum
 **/

// ��������� �������
define('DEBUG_MODE', 1);

# ---------------------------------------------------------- > ������ ��� PHP 4.4.4

if (version_compare(PHP_VERSION, '4.4.4')) {
    exit('For PHP 4.4.4 only!');
}

# ---------------------------------------------------------- > ��������� ������ ��� ������

// ������ ����������
define('APP_NAME',          'PhProxy');
define('APP_jVERSION',      '2');
define('APP_nVERSION',      '0');
define('APP_nBUILD',        4);
define('APP_sBUILD',        'Alpha1');
define('APP_dBUILD',        '09.02.2011');

// ���� � ����������� ��� ������
define('DS',                DIRECTORY_SEPARATOR);
define('APP_ROOT',          realpath('.'.DS) . DS);

// ���� � ����������� ����������
define('CT_APP_ROOT',       '');
define('CT_APP_ROOT_SRC',   CT_APP_ROOT . 'include' . DS);
define('CT_APP_ROOT_IMGS',  CT_APP_ROOT . 'srcs' . DS);


# ---------------------------------------------------------- > ����������� �������������

error_reporting(E_ALL | E_NOTICE);
ob_implicit_flush(1);
set_time_limit(0);
ini_set('display_errors',           True);
ini_set('register_argc_argv',       True);
ini_set('log_errors',               True);
ini_set('log_errors_max_len',       1024);
ini_set('error_log',                APP_ROOT. 'PhProxyLog.txt');

# ---------------------------------------------------------- > ����������� ����������� ������

require CT_APP_ROOT_SRC . 'configs.php';  
require CT_APP_ROOT_SRC . 'defines.php';  
require CT_APP_ROOT_SRC . 'functions.php';

# ---------------------------------------------------------- > �������������

// �������� �������������
event();
event('������� ������� ���������...');
event(' -- ������ PhProxy: ['.version().']');
event(' -- ������ PHP: ['.PHP_VERSION.']');
event(' -- ������ ��: ['.@php_uname().']');
event(' -- ���� �������: ['.APP_ROOT.']');

    // ��������� ���������� �� ���������� WinBindera
    if (!extension_loaded('winbinder')) {
        error_fatal('���������� ��� ������ � WinBinder �� ��������!');
    } else {
        event('���������� ��� ������ � WinBinder �������� � ���������� �������!');
    }

    // ���������� ����������� ����� WB
    if (!include CT_APP_ROOT_SRC . 'winbinder' . DS . 'winbinder.php') {
        error_fatal('������ ��� �������� ��������� WB ������!');
    } else {
        event('��������� WB ����� ���������� �������!');
    }
    
// ���������� GUI ����� � ����� ������� ������ ����������
require CT_APP_ROOT_SRC . 'classes' . DS . 'wb_window.class.php';
require CT_APP_ROOT_SRC . 'classes' . DS . 'application.class.php';

event('�������������: ������� �����������!');


# ---------------------------------------------------------- > �������!

// ������� ��������� ������ ������� �������
$phproxy = new PhProxy_Net();

    // ��������� ������� ������������ ��� ������ GUI
    if (!class_exists('WB_Window')) {
        error_fatal('�� ������ ����� GUI � ��������� ����������!');
    } else {
        event('����� GUI ������� ���������!');
    }


// ������� ������ �������� ����
event('������ �������� GUI ����������...');

$w_main = new WB_Window(0, AppWindow, WBC_TASKBAR | WBC_INVISIBLE);
    $w_main->title(version(cfg_get('gui_mainwin_title')));
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

/*// ������� ������ �����
$phroxy->gui_bold_font = wb_create_font("Tahoma", 8, BLACK, FTA_BOLD);

// ������� ������ ����������
#wb_set_font(wb_create_control($w_main->wobj, Label, 'PhProxy-������:', 10, 200, 110,  15, 100), $phroxy->gui_bold_font);
#wb_set_font(wb_create_control($w_main->wobj, Label, '����� ������:',   10, 215, 110,  15, 101), $phroxy->gui_bold_font);
#wb_set_font(wb_create_control($w_main->wobj, Label, '���������� �����:', 10, 230, 110,  15, 102), $phroxy->gui_bold_font);
#wb_set_font(wb_create_control($w_main->wobj, Label, '������:',        10, 245, 100,  15, 103), $phroxy->gui_bold_font);

// ������ ����
#wb_set_font(wb_create_control($w_main->wobj, Label,   '�����������:',   240, 200, 100,  15, 104), $phroxy->gui_bold_font);
#wb_set_font(wb_create_control($w_main->wobj, Label,  'PhProxy-UpTime:',   240, 215, 100,  15, 105), $phroxy->gui_bold_font);
#wb_set_font(wb_create_control($w_main->wobj, Label,  'PhProxy-������:',  240, 230, 100,  15, 106), $phroxy->gui_bold_font);
#wb_set_font(wb_create_control($w_main->wobj, Label,  '������:',          240, 245, 100,  15, 107), $phroxy->gui_bold_font);

// ������� ��������� ����������
#$phproxy->gui_server_state_ctrl =        wb_create_control($w_main->wobj, Label, '~', 125, 200, 100,  15, 110);
#$phproxy->gui_server_uptime_state_ctrl = wb_create_control($w_main->wobj, Label, '~', 125, 215, 100,  15, 111);
#$phproxy->gui_server_incom_state_ctrl =  wb_create_control($w_main->wobj, Label, '~', 125, 230, 100,  15, 111);

#$phproxy->gui_auth_state_ctrl   = wb_create_control($w_main->wobj, Label,   '~', 355, 200, 100,  15, 110);
#$phproxy->gui_uptime_state_ctrl = wb_create_control($w_main->wobj, Label, '~',   125, 215, 100,  15, 110);

// ������� ��������� ������ ����������� ����������
#$phproxy->system_timer = wb_create_timer($w_main->wobj, ID_SYSTEM_TIMER, APP_SYSTEM_TIMER_INT);
#app_refresh_info();
 *
 */
event('GUI ���� ������� ��������...');

// �������� ��������� ������� ��� ����
wb_set_handler($w_main->wobj, 'phproxy_eventsHandler');

// ��������� ���������� ������
$phproxy->client_timer = wb_create_timer($w_main->wobj, ID_CLIENT_TIMER, cfg_get('net_client_timer'));

// ���������� ���� � ������ � ����
$w_main->visible(1); $w_main->loop();

?>