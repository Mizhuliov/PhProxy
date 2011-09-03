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

// ------------------------------ ��������� �� ����������

// ������ �����������
define('APP_AUTH_TIMER_INT',         500);
// ��������� ������
define('APP_SYSTEM_TIMER_INT',       500);


// ------------------------------ ID ���������

// ������
// ���������� ��������
define('ID_SERVER_BSTART',  1001);
define('ID_SERVER_BSTOP',   1002);
// �����������-�����������
define('ID_BAUTH_REGDO',     1003);


// ���� ����� - �����������
define('ID_IAUTH_EMAIL',    6001);
define('ID_IAUTH_PASS',     6002);


// �������
define('ID_SOCKET_TIMER',   2001);
define('ID_CLIENT_TIMER',   2002);

// ������������� ������ - ������� ����
define('ID_FRAME_SERVER',   3001);
define('ID_FRAME_AUTH',     3002);
define('ID_FRAME_STATE',    3003);

// �������� �������
define('ID_LOGO',           4001);

// ������ �����������
define('ID_LAUTH_EMAIL',    5001);
define('ID_LAUTH_PASS',     5002);


// ------------------------------ �����������

// ������������� ������
define('L_CFSERVER',            '���������� PhProxy');
define('L_CFAUTH',              '������ ��� ����������� �� �������');
#define('L_CSTATE',              '��������� PhProxy');

// ������ ���������� ��������
define('L_BSTART',              '��������� ������');
define('L_BSTART_DES',          '����������� �� ������� � ������ ������������� ���������� ������');
define('L_BSTOP',               '���������� ������');
define('L_BSTOP_DES',           '������ �� ������� � ��������� ������������� ���������� ������');

// ���� �����������
define('L_LAUTH_EMAIL',         'E-Mail:');
define('L_LAUTH_PASS',          '������:');
define('L_BAUTH_REGDO',         '�����������');



?>