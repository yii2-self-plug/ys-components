<?php
    use yii\helpers\Json;
    $formData = isset($options['formData']) ? $options['formData'] : [];
    $view->registerJs("
        var imageEdit".$key." = new imageEdit('".$key."');
        imageEdit".$key.".cropperServer = \"".$cropperServer."\";
        imageEdit".$key.".cropperOptioin = ".$cropperOptioin.";
        var upload".$key." = new upload('".$key."',imageEdit".$key.");
        upload".$key.".init({
            auto:".$options['auto'].",
            swf:'".$asset['webuploader']->baseUrl.'/'.$asset['webuploader']->swf."',
            server:'".$server."',
            multiple:".$options['multiple'].",
            accept:".$options['accept'].",
            name:'".$name."',
            formData:".Json::encode($formData).",
        });
        var _upload".$key." = upload".$key.";
        $(function(){
            $('#cropperModal').modal('hide');
            $('body').on('click','#ctlBtn".$key."',function(){
                console.log(upload".$key.".state);
                if (upload".$key.".state === 'pending' ) {
                    _upload".$key.".uploader.upload();
                }
            });
            $('#".$boxid."').on( 'mouseenter', function() {
                $('#btns".$key."').stop().animate({height: 30});
            });
            $('#".$boxid."').on( 'mouseleave', function() {
                $('#btns".$key."').stop().animate({height: 0});
            });
            $('#btns".$key."').on( 'click', 'span', function() {
                var index = $(this).index();
                switch ( index ) {
                    case 0:
                        $(this).closest('#".$boxid."').remove();
                        return;
                    case 1:
                        var file = {'id':'".$boxid."'};
                        imageEdit".$key.".init(file);
                        return;
                }
            });
        });
    ");
?>
<div id="uploader" class="wu-example">
    <!--用来存放文件信息-->
    <div id="fileList<?php echo $key ?>" class="uploader-list fileList">
        <?php
            if ($value) {
                echo "<div id=\"".$boxid."\" class=\"file-item thumbnail\">
                        <img src=\"".$value."\" style=\"width:100px;height:100px;\">
                        <input type=\"hidden\" class=\"success\" name=\"".$name."\" value=\"".$value."\"/>
                        <div id=\"btns\" class=\"file-panel\">
                            <span class=\"cancel\">删除</span>
                            <span class=\"edit\">编辑</span>
                        </div>
                    </div>";
            }
        ?>
    </div>
    <div class="btns" style="width:100%;float:left;">
        <div id="filePicker<?php echo $key;?>" style="float:left;">选择文件</div>
        <?php if($options['auto'] == 'false'){
                echo '<button id="ctlBtn'.$key.'" class="btn btn-default" style="float:left;margin-left:10px;padding-top:10px;" type="button">开始上传</button>'; 
            } 
        ?>
    </div>
</div>
<style type="text/css">
    #editImage{
        max-width: 100%;
    }
</style>
<div class="modal fade" id="cropperModal<?php echo $key ?>" tabindex="-1" role="dialog" aria-labelledby="cropperModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="cropperModalLabel">
                    编辑图片
                </h4>
                <button type="button" class="btn btn-default cropperEnlarge<?php echo $key; ?>">放大</button>
                <button type="button" class="btn btn-default cropperNarrow<?php echo $key; ?>">缩小</button>
                <button type="button" class="btn btn-default cropperRotate-s<?php echo $key; ?>">旋转(顺)</button>
                <button type="button" class="btn btn-default cropperRotate-n<?php echo $key; ?>">旋转(逆)</button>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×
                </button>
            </div>
            <div class="modal-body" style="text-align:center;max-height:550px">
                <img id="editImage<?php echo $key; ?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    关闭
                </button>
                <button type="button" class="btn btn-primary submitCorpper<?php echo $key; ?>">
                    提交
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="cropperAlert" tabindex="-1" role="dialog" aria-labelledby="cropperAlertLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="cropperAlertLabel">提示</h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
<script>

</script>