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

// ------------------------------------------------------------- >> PHPROXY



// ������� �������� ���������
function phproxy_stop()
{
    global $w_main, $phproxy;
    event('������� ���������� ������ ��������� �� ���������� ������������...');

        // ��������� ������ ������
        if ($phproxy->socket) {
            return error('����� ������� �� ��������� �� ������ ���������� PhProxy-������!');
        }

    // ��������� ������ GUI
    $w_main->close();
    event('���������� ��������� ������� ����������!');
}

// ������� ������� �������
function phproxy_server_start()
{
    global $phproxy, $w_main;

    // �����
    event('������� ������� PhProxy ������� � ������������ ����������...');
    $w_main->status('������� ������� PhProxy �������...');

    // ������� ��������� ������
    $result = $phproxy->server_start();
        if (!$result) { // ������ ������� �� ������
            event('������� ������� ������� ����������� ��������:');
            return error('������: '.$phproxy->error);
        }

    event('������ ������� �������!');
    $w_main->status('��������� ������ ������� �������!');

    
    // ������� ��������������
    event('������� ����������� �� �������...');


    // ������� ����������� ������, ��� ����� ����� �����������
    $phproxy->auth_state  = 1;
    $phproxy->auth_email  = 'guest@guest';
    $phproxy->auth_pass   = 'guest';
    $phproxy->auth_key    = 'auth_key';
    $phproxy->auth_expire = time()+180;
    
    event('����������� ������� ���������!');

    
    // ��������� ������ ������� �� ������� � ������
    event('�������� ������� ������...');
    $phproxy->socket_timer = wb_create_timer($w_main->wobj, ID_SOCKET_TIMER, cfg_get('net_server_socket_timer'));
        if (!$phproxy->socket_timer) {
            event('������ ��� �������� �������!');
            error('�� ������� ������� ������. ������������� ������ �� ��������!');
        } else {
            event('������ ������ ������� ������!');
        }

    wb_set_enabled($w_main->serverButtonStop, 1);
    wb_set_enabled($w_main->serverButtonStart, 0);

    $w_main->status('������ ������� �������!');
    return true;
}

// ������� ��������� �������
function phproxy_server_stop()
{
    global $phproxy, $w_main;
    event('������� ��������� PhProxy ������� � ������������ ����������...');
    $w_main->status('������� ���������� ������...');

    $result = $phproxy->server_stop();
        if (!$result) {
           event('������� ��������� ������� ����������� ��������:');
           $w_main->status('������ ��� ��������� �������!');
           return error('������: '.$phproxy->error);
        }

    $w_main->status('������ ������� ����������!');
    event('������ ������� ����������!');
    wb_set_enabled($w_main->serverButtonStop,  0);
    wb_set_enabled($w_main->serverButtonStart, 1);
    return true;
}



// ------------------------------------------------------------- >> ������, �����������, ������

// ������� ����������� ������ �������
function event($txt = false)
{
    if ($txt == false) {
        $txt = "\r\n\r\n".str_repeat('-', 50)."\r\n\r\n";
    } else {
        $tx = 'EVENT - ['.$txt.']';
    }

    error_log($txt);
}

// ��������� ��������� ������ - ���������
function error_fatal($error)
{
    error_log('FATAL - ['.$error.']');
    error_gui($error.PHP_EOL.'���������� ������...', ' - ��������� ��������� ������', WBC_STOP);
        exit;
}

function error($error)
{
    error_log('ERROR - ['.$error.']');
    error_gui($error, ' - ��������� ������', WBC_WARNING);
    return false;
}

// ���������� ����������� ������ � �������
function error_gui($text, $title, $type)
{
    global $w_main;
        if (!function_exists('wb_message_box')) {
            return false;
        }

    if (isset($w_main) && isset($w_main->wobj)) {
        $ref = $w_main->wobj;
    } else {
        $ref = 0;
    }

    return wb_message_box($ref, $text, version('%an%/%avj%.%avn%.%avb%').$title, $type);
}

// ------------------------------------------------------------- >> ���������


// �������� �������� � ��������
function cfg_get($param)
{
    global $config;
        if (!isset($config[$param])) {
            error_fatal('����������� �������������� �������� ['.$param.']...');
        }
    return $config[$param];
}

// ------------------------------------------------------------- >> ������ �������

// ������� ��������� ������ �� �������
function version($format = '%an%/%avj%.%avn%.%avb% %avs% (%avd%)')
{
    return str_replace(
        array('%an%', '%avj%', '%avn%', '%avb%', '%avs%','%avd%'),
        array(APP_NAME, APP_jVERSION, APP_nVERSION, APP_nBUILD, APP_sBUILD, APP_dBUILD),
        $format
    );
}

// ������� ������� ���������
function timer()
{
    list($s, $ms) = explode(" ",  microtime());
    return (float)$s + (float)$ms;
}


/*
// ����������� �� �������
function server_auth($timer)
{
    global $phproxy, $w_main;


    // ������ ������ - ��������� � �����������
    if (!$timer) {
        event('������� ����������� � ������������ ����������...');

            if ($phproxy->auth_state) {
                error('�� ��� ������������� �� �������!'."\r\n".
                      '��� ��������� ��������� �������� ���������� � ����������!');
                return false;
            }

            if ($phproxy->server_state) {
                error('������ �������, � �� �� �����������...'."\r\n".
                      '���� ���� �������, ��������!');
                return false;
            }

        // �������� ���� � ������ �� ����
        $phproxy->auth_email = 'email';
        $phproxy->auth_pass  = 'password';

        // ������� ������ �����������
        $phproxy->auth_timer = wb_create_timer($w_main->wobj, ID_AUTH_TIMER, APP_AUTH_TIMER_INT);

        // ���������� ���� ��������� �����������
        $phproxy->auth_stage = 1;

        $w_main->status('���� ����������� �� �������...');
        return true;
    }





    // ������ ������ � ��������

        // ��� -1 - ��������� ������
        if ($phproxy->auth_stage == -1) {
            $w_main->status('������ ��� �����������...');
            wb_destroy_timer($w_main->wobj, ID_AUTH_TIMER);
            error('�������������� �� �������. ����������� � ����.');
            $phproxy->auth_stage = 0;
        }

        // ���� 1 - ��������� ����������
        if ($phproxy->auth_stage == 1) {
            $w_main->status('������� ���������� � ��������...');

            // ������� ����� ��������� ����������
            $phproxy->auth_cnx = $phproxy->client_open(cfg_get('net_remote_domain'), cfg_get('net_remote_port'), cfg_get('net_remote_timeout'));
                if (!$phproxy->auth_cnx) { // �� �������
                    $phproxy->auth_stage = -1;
                    return false;
                }

            $phproxy->auth_stage = 2;
            $w_main->status('���������� � �������� ������������...');
            return false;
        }


        // ���� 2 - �������� ������
        if ($phproxy->auth_stage == 2) {
            $w_main->status('������� �������� ������...');

            // �������� ������
            $email  = base64_encode($phproxy->auth_email);
            $passMD = base64_encode(md5($phproxy->auth_pass));

            // �������� ���� ������
            $post = 'act=auth&email='.$email.'&pass='.$passMD.'&version='.APP_nBUILD;

            // ���������� ������
            $result = $phproxy->client_send($phproxy->auth_cnx, $post);
                if (!$result) {
                    client_close($phproxy->auth_cnx);
                    $phproxy->auth_stage = -1;
                    return false;
                }

            $phproxy->auth_stage = 3;
            $w_main->status('������ ������� �����������...');
                return false;
        }


        // ���� 3 - ������ ������
        if ($phproxy->auth_stage == 3) {
            $w_main->status('��������� ������ ['.$phproxy->auth_sub_stage.']...');

                // ���� ���� ��� ������
                if (!$data = $phproxy->client_read($phproxy->auth_cnx)) {
                    $phproxy->auth_stage = 4;
                    $w_main->status('������ ������� ����������...');
                } else {
                    $phproxy->auth_data .= $data;
                    $phproxy->auth_sub_stage++;
                }

        }

        // ���� 3 - ������ ������
        if ($phproxy->auth_stage == 4) {

            // ��������� ����������
            $phproxy->client_close($phproxy->auth_cnx);

            // ������� ������
            wb_destroy_timer($w_main->wobj, ID_AUTH_TIMER);

            // ������ ���������� ������
            $data = $phproxy->html_parse_response($phproxy->auth_data);
            // ����������
            $phproxy->auth_stage = $phproxy->auth_sub_stage = 0; $phproxy->auth_data = '';

                if ($data === false) {
                    error('������ ��� ������� �����������!'."\r\n".'����������� � ����...');
                    $w_main->status('�����������: ������');
                    return false;
                }

            // ������ ������������
            if ($data['state'] == 'error') {
                if (!isset($data['error'])) {
                    $error = '����������� ������';
                } else {
                    $error = $data['error'];
                }

                $w_main->status('�����������: ������');
                error('������ �����������:'."\r\n".$error);
                return false;
            }

            // ��������� (
            if ($data['state'] != 'ok') {
                $w_main->status('�����������: ������');
                error('������ �����������:'."\r\n".'�� ������� ������ ����� �������...');
                return false;
            }

            // ����������� ������ �������
            $phproxy->auth_key    = $data['authkey'];
            $phproxy->auth_expire = (int)$data['expire'];

            // ���� �������� �����������
            $phproxy->auth_state = 1;


                // ������ ������ ����������� �� "�����"
                wb_set_text($w_main->serverButtonAuthDo, '�����');


            $w_main->status('�����������: ��������');
            return true;
        }


    return true;
}




// ���������� ��������� ����������
function app_refresh_info()
{
    global $phproxy;

    // ��������� �������
    wb_set_text($phproxy->gui_server_state_ctrl, ($phproxy->server_state) ? '�������' : '����������');


    // ����� ������ �������
        if ($phproxy->server_started == 0) {
            $temp_time = time();
        } else {
            $temp_time = $phproxy->server_started;
        }
    wb_set_text($phproxy->gui_server_uptime_state_ctrl, gmdate('H:i:s', time()-$temp_time));

    // ���-�� ��������
    wb_set_text($phproxy->gui_server_incom_state_ctrl, $phproxy->server_incoming);

    // ��������� �����������
    wb_set_text($phproxy->gui_auth_state_ctrl, ($phproxy->auth_state) ?     '�����������' : '�� �����������');


}





*/
?>