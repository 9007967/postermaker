# Postermaker 海报生成器
A poster make base on gd lib. (PHP)  一个用php生成海报的神器

# Useage 用法
比如在文件someaction.php中使用：
```
$poser = new \Ryan\postermaker(800, 1100);
$poser
    ->addImg('./bg.jpg', [0,0], [800, 1100])
    ->addImg('./data/upload/cover.jpg', [30,30],[740, 500])
    ->addImg('./data/upload/avatar.png', [520,620],[200, 200])
    ->addText('2020雅思口语刷题班', 30, [30,610], [255, 255, 255])
    ->addText('授课老师：Rico', 24, [30,720], [255, 255, 255])
    ->addText('coolhand', 24, [30,870], [255, 255, 255])
    ->addText('邀请你一起学习', 24, [30, 910], [255, 255, 255])
    ->addText('长按扫码听课', 24, [30,950], [255, 255, 255])
    ->addQrCode('http://com/123', [500,800],[250,250])
    ->render();
```
### addImg
按大小创建一个海报 
```
$poser = new \Ryan\postermaker(800, 1100); // (width, height)
```
### addImg
添加图片 (图片路径, [x坐标, y坐标], [width, height])
```
$poser->addImg('./data/upload/cover.jpg', [30,30],[740, 500])
```
### addText
添加图片 (文字内容, 字体大小, [x坐标, y坐标], 颜色[R,G,B])
```
$poser->addText('2020雅思口语刷题班', 30, [30,610], [255, 255, 255])
```
### addQrCode
添加二维码 (文字内容, [x坐标, y坐标], [width, height])
```
$poser->addQrCode('http://com/123', [500,800],[250,250])
```
### render
添加图片 (文字内容, 字体大小, [x坐标, y坐标], 颜色[R,G,B])
```
$poser->render('./save.png'); // 保持为图片
// or
$poser->render(); // show image in html: `<img src="someaction.php" style="border-radius: 20px;"/>`
```

# Author
Ryan
Email:541720500@qq.com