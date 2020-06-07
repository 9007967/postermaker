<?php

namespace PosterMaker;
class PosterMaker
{
    public $bg; // 背景图

    protected $extensions = [
        IMAGETYPE_GIF       => "gif"
        , IMAGETYPE_JPEG    => "jpg"
        , IMAGETYPE_PNG     => "png"
        , IMAGETYPE_SWF     => "swf"
        , IMAGETYPE_PSD     => "psd"
        , IMAGETYPE_BMP     => "bmp"
        , IMAGETYPE_TIFF_II => "tiff"
        , IMAGETYPE_TIFF_MM => "tiff"
        , IMAGETYPE_JPC     => "jpc"
        , IMAGETYPE_JP2     => "jp2"
        , IMAGETYPE_JPX     => "jpx"
        , IMAGETYPE_JB2     => "jb2"
        , IMAGETYPE_SWC     => "swc"
        , IMAGETYPE_IFF     => "iff"
        , IMAGETYPE_WBMP    => "wbmp"
        , IMAGETYPE_XBM     => "xbm"
        , IMAGETYPE_ICO     => "ico"
        , IMAGETYPE_WEBP    => "webp"
    ];

    /**
     * 构造函数
     * @param $w        int 宽度(px)
     * @param $h        int 高度(px)
     * @param $bg_color array RGB color value
     */
    public function __construct(int $w, int $h, $bg_color = [ 255, 255, 255 ])
    {
        $this->createBg($w, $h, $bg_color);
    }

    /**
     * 填充画布背景
     * @param $w        int 宽度(px)
     * @param $h        int 高度(px)
     * @param $bg_color array [R,G,B] color value
     */
    protected function createBg($w, $h, $bg_color)
    {
        $this->bg = imagecreatetruecolor($w, $h);
        $c        = imagecolorallocate($this->bg, $bg_color[0], $bg_color[1], $bg_color[2]);
        imagefill($this->bg, 0, 0, $c);
    }

    /**
     * 添加图片
     * @param      $img_path string 图片路径
     * @param      $xy       array 坐标[x坐标，y坐标]
     * @param      $size_wh  array 尺寸[width, height]
     * @param bool $status
     * @return
     */
    public function addImg($img_path, $xy = [ 0, 0 ], $size_wh = [ 100, 100 ], bool $status = true)
    {
        if ($status) {
            [ $l_w, $l_h ] = getimagesize($img_path);
            $img = $this->createImageFromFile($img_path);
            imagecopyresized($this->bg, $img, $xy[0], $xy[1], 0, 0, $size_wh[0], $size_wh[1], $l_w, $l_h);
            imagedestroy($img);
        }
        return $this;
    }

    /**
     * 添加文字
     * @param        $text          string 文字
     * @param        $size          int 文字大小
     * @param        $xy            array 坐标[x坐标，y坐标]
     * @param        $color         string color value
     * @param        $font_file     string 字体路径
     * @param        $angle         int 文字旋转角度
     * @param        $txt_max_width int 文字区块最大宽度 超过换行
     * @param        $shadow        bool
     * @return
     */
    public function addText($text, $size = 14, $xy = [ 0, 0 ], string $color = '#000000', $font_file = '', $angle = 0, $txt_max_width = null, $shadow = false)
    {
        if ($font_file == '')
            $font_file = __DIR__ . DIRECTORY_SEPARATOR . 'msyh.ttc';

        if ($txt_max_width) {
            $str = "";
            for ($i = 0; $i < mb_strlen($text); $i++) {
                $letter[] = mb_substr($text, $i, 1);
            }

            foreach ($letter as $l) {
                $teststr = $str . " " . $l;
                $testbox = imagettfbbox($size, $angle, $font_file, $teststr);
                if (($testbox[2] > $txt_max_width) && ($str !== "")) {
                    $str .= "\r\n";
                }
                $str .= $l;
            }
            $text = $str;
        }

        if (is_string($color) && 0 === strpos($color, '#')) {
            $color = str_split(substr($color, 1), 2);
            $color = array_map('hexdec', $color);
            if (empty($color[3]) || $color[3] > 127) {
                $color[3] = 0;
            }
        } else if (!is_array($color)) {
            throw new ImageException('错误的颜色值');
        }

        $font_color = ImageColorAllocate($this->bg, $color[0], $color[1], $color[2]);
        if ($shadow) {
            $shadowCol = imagecolorallocatealpha($this->bg, 0, 0, 0, 80);
            imagettftext($this->bg, $size, $angle, $xy[0] + 1, $xy[1] + 1, $shadowCol, $font_file, $text);
        }
        imagettftext($this->bg, $size, $angle, $xy[0], $xy[1], $font_color, $font_file, $text);
        return $this;
    }

    /**
     * 添加二维码
     * @param $text    string 文字
     * @param $xy      array 坐标[x坐标，y坐标]
     * @param $size_wh array 尺寸[width, height]
     */
    public function addQrCode($text, $xy = [ 0, 0 ], $size_wh = [ 100, 100 ])
    {
        require_once "QRcode.php";
        if (!is_readable('./tempqr'))
            mkdir('./tempqr', 0700);
        $tmp_name = './tempqr/' . md5($text) . '.png';
        \QRcode::png($text, $tmp_name, 0, 4);
        return $this->addImg($tmp_name, $xy, $size_wh);
    }

    /**
     * 输出图片
     * @param $file_name string 最后保存海报的路径，留空表示直接向浏览器输出图片
     */
    public function render($file_name = '')
    {
        if ($file_name != '') {
            imagepng($this->bg, $file_name);
        } else {
            Header("Content-Type: image/png");
            imagepng($this->bg);
        }
        imagedestroy($this->bg);
    }

    /**
     * 从图片文件创建Image资源
     * @param $file 图片文件，支持url
     * @return bool|resource   成功返回图片image资源，失败返回false
     */
    public function createImageFromFile($file)
    {
        if (preg_match('/http(s)?:\/\//', $file)) {
            $fileSuffix = $this->getNetworkImgType($file);
        } else {
            $fileSuffix = $this->extensions[@exif_imagetype($file)];
        }

        if (!$fileSuffix) return false;
        switch ($fileSuffix) {
            case 'jpg':
            case 'jpeg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'png':
                $theImage = @imagecreatefrompng($file);
                break;
            case 'gif':
                $theImage = @imagecreatefromgif($file);
                break;
            default:
                $theImage = @imagecreatefromstring(file_get_contents($file));
                break;
        }
        return $theImage;
    }

    /**
     * 获取网络图片类型
     * @param $url  网络图片url,支持不带后缀名url
     * @return bool
     */
    public function getNetworkImgType($url)
    {
        $ch = curl_init();                   //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //设置需要获取的URL
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //支持https
        curl_exec($ch);                                  // 执行curl会话
        $http_code = curl_getinfo($ch);                  //获取curl连接资源句柄信息
        curl_close($ch);                                 // 关闭资源连接
        if ($http_code['http_code'] == 200) {
            $theImgType = explode('/', $http_code['content_type']);
            if ($theImgType[0] == 'image') {
                return $theImgType[1];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
