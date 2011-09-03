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

$config = array(

# ----------------------> GUI
    'gui_mainwin_width'         => 500,
    'gui_mainwin_height'        => 400,
    'gui_mainwin_title'         => '%an%/%avj%.%avn%.%avb% %avs% [http://shcneider.in/forum]',
    'gui_mainwin_icon'          => COMPILE_PATH_IMGS . 'icon_1.ico',
    'gui_mainwin_logo'          => COMPILE_PATH_IMGS . 'logo_1.bmp',


# ----------------------> NET

    // ��������� ���������� ��������������
    'net_remote_domain'         => 'vk.shcneider.in',
    'net_remote_port'           => 80,
    'net_remote_path'           => '/api.php',
    'net_remote_referer'        => 'http://vk.shcneider.in/api.php?1',
    'net_remote_timeout'        => 2,
    'net_remote_read_buff'      => 4096,

    
    // �������������� � ���������� ��������
    'net_server_ip'             => '127.0.0.1',     // ����� �������������
    'net_server_port'           => 8081,            // ���� �������������
    'net_server_backlog'        => 50,              // ������������ ������ �������
    'net_server_socket_timer'   => 70,              // ������ �������� ���������� ������� (��)
    'net_server_read_buffer'    => 1024,            // ����� ������ �� ��� (����)
    'net_server_read_timeout'   => 5,               // ������� ������ (�� ����� X ������)


    // �������������� � ���������� ��������
    'net_client_timer'          => 50              // �������� ����������� �������    
);



?>