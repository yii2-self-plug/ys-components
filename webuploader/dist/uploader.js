var upload = function(key,imageEdit){
    this.uploader = {};
    this.state = 'pending';
    this.init = function(options){
        this.uploader = WebUploader.create({
            // 选完文件后，是否自动上传。
            auto: options.auto,
            // swf文件路径
            swf:options.swf,
            // 文件接收服务端。
            server: options.server,
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: {
                id:'#filePicker'+key,
                multiple:options.multiple
            },
            // 只允许选择图片文件。
            accept: options.accept,
            formData: options.formData
        });
        // 当有文件添加进来的时候
        var _that = this;
        this.uploader.on( 'fileQueued', function( file ) {
            var fileName = file.name;
            var $li = $(
                    '<div id="' + file.id + '" class="file-item thumbnail">' +
                        '<img>' +
                    '</div>'
                    ),$img = $li.find('img'),$list = $("#fileList"+key);

            var $btns = $('<div id="'+key+'" class="file-panel">' +
                        '<span class="cancel">删除</span><span class="edit">编辑</span></div>').appendTo( $li );
            // $list为容器jQuery实例
            if (!options.multiple) {
                $list.empty();
            }
            $list.append( $li );
            // 创建缩略图
            // 如果为非图片文件，可以不用调用此方法。
            // thumbnailWidth x thumbnailHeight 为 100 x 100
            _that.uploader.makeThumb( file, function( error, src ) {
                if ( error ) {
                    $img.replaceWith('<span>不能预览</span>');
                    return;
                }
                $img.attr( 'src', src );
            }, 100, 100 );

            $li.on( 'mouseenter', function() {
                $btns.stop().animate({height: 30});
            });
            $li.on( 'mouseleave', function() {
                $btns.stop().animate({height: 0});
            });
            $btns.on( 'click', 'span', function() {
                var index = $(this).index();
                switch ( index ) {
                    case 0:
                        _that.uploader.removeFile(file,true);
                        _that.uploader.reset();
                        this.state = 'pending';
                        $("#"+file.id).remove();
                        return;
                    case 1:
                        imageEdit.init(file);
                        return;
                }
            });
        });
        // 文件上传过程中创建进度条实时显示。
        this.uploader.on( 'uploadProgress', function( file, percentage ) {
            var $li = $( '#'+file.id ),
                $percent = $li.find('.progress span');
            // 避免重复创建
            if ( !$percent.length ) {
                $percent = $('<p class="progress"><span></span></p>')
                        .appendTo( $li )
                        .find('span');
            }

            $percent.css( 'width', percentage * 100 + '%' );
        });
        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
        this.uploader.on( 'uploadSuccess', function( file , response ) {
            var inputHtml = '<input type="hidden" class="success" name="'+options.name+'" value="'+response.url+'"/>';
            $( '#'+file.id ).addClass('upload-state-done');
            if (response.state == 'SUCCESS') {
                $( '#'+file.id ).append(inputHtml);
            }
        });
        // 文件上传失败删除图片。
        this.uploader.on( 'uploadError', function( file ) {
            var $li = $( '#'+file.id ),
                $error = $li.find('div.error');

            // 避免重复创建
            if ( !$error.length ) {
                $error = $('<div class="error"></div>').appendTo( $li );
            }
            $error.text('上传失败');
            _that.uploader.reset();
            this.state = 'pending';
            setTimeout(function(){
               $li.remove(); 
            },2000);
        });
        // 完成上传完了，成功或者失败，先删除进度条。
        this.uploader.on( 'uploadComplete', function( file ) {
            $( '#'+file.id ).find('.progress').remove();
        });
        this.uploader.on( 'all', function( type ) {
            if ( type === 'startUpload' ) {
                this.state = 'uploading';
            } else if ( type === 'stopUpload' ) {
                this.state = 'paused';
            } else if ( type === 'uploadFinished' ) {
                this.state = 'done';
            }
        });
    }
};
