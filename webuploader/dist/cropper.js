var imageEdit = function(key){
	this.cropper;
	this.imgUrl;
	this.cropperServer;
	this.cropperOptioin;
	this.file;
	this.modalName = "#cropperModal"+key;
	this.img = "#editImage"+key;
	this.submitBtn = ".submitCorpper"+key;
	this.alert = "#cropperAlert"+key;
	this.enlargeBtn = ".cropperEnlarge"+key;
	this.narrowBtn = ".cropperNarrow"+key;
	this.rotateSBtn = ".cropperRotate-s"+key;
	this.rotateNBtn = ".cropperRotate-n"+key;
	this.init = function(file){
		var _that = this;
		this.file = file;
		//判断图片是否上传
		var inputImage = $('#'+file.id).find('.success');
		if (inputImage.attr('name') != undefined) {
			this.modal(inputImage.val());
		}
		$('body').on('click',this.submitBtn,function(){
			_that.submit();
		});
	}
	this.modal = function(imgUrl){
		var _that = this;
		this.imgUrl = imgUrl;
		$(this.img).attr('src',imgUrl);
		$(this.modalName).modal('show');
		$(".modal-backdrop").hide();
		$(this.modalName).on('hide.bs.modal', function () {
	    	$(_that.img).cropper('destroy');
	  	});
	  	$(this.modalName).on('shown.bs.modal', function () {
	  		_that.cropper();
		});
	}
	this.cropper = function(){
		var _that = this;
		var width = $(this.modalName).find('.modal-body').width() - 15;
		var height = $(this.modalName).find('.modal-body').height() - 15;
		$(this.img).cropper({
			aspectRatio: _that.cropperOptioin.width / _that.cropperOptioin.height,
			checkImageOrigin:false,
			zoomable:true,
			zoomOnWheel:false,
			minContainerWidth:width,
			minContainerHeight:height,
			crop: function(e) {
			},
			build:function(){
				$('body').on('click',this.enlargeBtn,function(){
					$(_that.img).cropper('zoom',0.1);
				});
				$('body').on('click',this.narrowBtn,function(){
					$(_that.img).cropper('zoom',-0.1);
				});
				$('body').on('click',this.rotateSBtn,function(){
					$(_that.img).cropper('rotate',10);
				});
				$('body').on('click',this.rotateNBtn,function(){
					$(_that.img).cropper('rotate',-10);
				});
			}
		});
	}
	this.submit = function(){
		var _that = this;
		var data = $(this.img).cropper('getData');
		var imageData = $(this.img).cropper('getImageData');
		data['imgUrl'] = this.imgUrl;
		data['options'] = this.cropperOptioin;
		if (!this.cropperServer) {
			this.showAlert('上传服务器不存在');
			return false;
		}
		$.post(this.cropperServer,data,function(rdata){
			if (rdata.code == 1) {
				$('body').off('click',_that.submitBtn);
				var url = rdata.url;
				$('#'+_that.file.id).find('.success').val(url);
				var img = $('#'+_that.file.id).find('img');
				img.attr('src',url);
				img.css({width:100,height:100});
				$(_that.modalName).modal('hide');
			}else{
				_that.showAlert(rdata.message);
			}
		},'json');
	}
	this.showAlert = function(message){
		$(this.alert).find('.modal-body').html(message);
		$(this.alert).modal('show');
	}
};
