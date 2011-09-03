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



// ����� �������� �������� ����
class WB_Window {

// ��������?
var $builded = false;

// ������ �� ����
var $wobj = 0;

// �������� ����
var $parent = 0;

// ��� ����
var $type = 0;

// ��������� ����
var $title = '';

// ������� Y � Y
var $pos_x, $pos_y =  0;

// �������
var $size_x, $size_y = 0;

// �������������� �����
var $flags = 0;

// ������
var $icon = null;



// �������� - ������������� � ��������� �������
var $serverButtonStart = 0;
var $serverButtonStop  = 0;

// �������  - ���������
var $statusbar = 0;

// �������� - ���� ����� ��������������
var $serverButtinAuthEmail = 0;
var $serverButtonAuthPass  = 0;

// �������� - ������ ����������� � �����������
var $serverButtonRegDo  = 0;


    // ����������� ����
    function WB_Window($parent = 0, $type = 1, $flags = null)
    {
        $this->parent = $parent;
        $this->type   = $type;
        $this->flags  = $flags;
    }

    // ������� ����
    function build()
    {
        // ���� ���� ��� ��������
        if ($this->builded) {
            return false;
        }

        $this->wobj = wb_create_window(
            $this->parent, $this->type, $this->title, $this->pos_x, $this->pos_y, $this->size_x, $this->size_y, $this->flags
         );

            if ($this->icon) {
                wb_set_image($this->wobj, $this->icon);
            }

        return $this->builded = true;
    }

    // ����
    function loop()
    {
        // ���� ��� ����, ��� ������ � �����
        if (!$this->builded) {
            return false;
        }

        wb_main_loop();
    }

    // ��������� ���� - ���������� ��� ��������
    function title($title = null)
    {
        // ���� ���� ��� �� ���� �����������
        if (!$this->builded) {
            $return = ($title) ? $this->title = $title : false;
            return $return;
        }

            // ��������� ���������
            if ($title) {
                return wb_set_text($this->wobj, $title);
            }

        return wb_get_text($this->wobj, $title);
    }

    // ���������� ��� �������� �������
    function position($x = null, $y = null)
    {
        // ���� ��������� �� ���������
        if (!$x && !$y) {
            if (!$this->builded) {
                return array($this->pos_x, $this->pos_y);
            }
            return wb_get_position($this->wobj);
        }

        // ���� ��������� �������� - ������� �� ������������ :)
        $x = (!$x) ? WBC_CENTER : $x;
        $y = (!$y) ? WBC_CENTER : $y;

            // ���� ���� ��� �� ����������
            if (!$this->builded) {
                $this->pos_x = $x; $this->pos_y = $y;
                return true;
            }

        return wb_set_position($this->wobj, $x, $y);
    }

    // ���������� ��� �������� ������
    function size($x = null, $y = null)
    {
        // ���� �� ��� ��������� ���������
        if (!$x && !$y) {
            if (!$this->builded) {
                return array($this->size_x, $this->size_y);
            }
            return wb_get_size($this->wobj);
        }

        // �����������
        $x = (!$x) ? WBC_NORMAL : $x;
        $y = (!$y) ? WBC_NORMAL : $y;

            // ���� ���� ��� �� ���� ����������
            if (!$this->builded) {
                $this->size_x = $x; $this->size_y = $y;
                return true;
            }

        return wb_set_size($this->wobj, $x, $y);
    }

    // ���������� ������
    function icon($icon = null)
    {
        // ���� ���� ��� �� ��������
        if (!$this->builded) {
            return $this->icon = $icon;
        }

        return wb_set_image($this->wobj, $icon);
    }

    // ��������� �������� ����������
    function status($text1)
    {
        wb_create_items($this->statusbar, array(
            array($text1, 340),
            array(' Alex Shcneider � 2009-2011 ')
        ));
    }

    // ���������� ���������� ����
    function visible($bool)
    {
        return wb_set_visible($this->wobj, $bool);
    }

    // ���������� ����
    function close()
    {
        wb_destroy_window($this->wobj);
    }

}





?>