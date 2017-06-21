
<?php global $_url; ?>
<div class="magnaTabs2">
	<ul>
		<li <?php echo $_url['subview']=='orderlist'? 'class="selected"':''?> >
		    <a title="<?php echo ML_AMAZON_SHIPPINGLABEL_UPLOAD_ORDERLIST ?>" class="breadcrumb" href=""><?php echo ML_AMAZON_SHIPPINGLABEL_UPLOAD_ORDERLIST ?></a>
		</li>
		<li <?php echo $_url['subview']=='form'? 'class="selected"':''?>>
			<a title="<?php echo ML_AMAZON_SHIPPINGLABEL_UPLOAD_FORM ?>" class="breadcrumb" href=""><?php echo ML_AMAZON_SHIPPINGLABEL_UPLOAD_FORM ?></a>
		</li>
		<li <?php echo $_url['subview']=='shippingmethod'? 'class="selected"':''?> >
			<a title="<?php echo ML_AMAZON_SHIPPINGLABEL_UPLOAD_SHIPPINGMETHOD ?>" class="breadcrumb" href="" ><?php echo ML_AMAZON_SHIPPINGLABEL_UPLOAD_SHIPPINGMETHOD ?></a>
		</li>
		<li <?php echo $_url['subview']=='summary'? 'class="selected"':''?> >
			<a title="<?php echo ML_AMAZON_SHIPPINGLABEL_UPLOAD_SUMMARY ?>" class="breadcrumb" href="" ><?php echo ML_AMAZON_SHIPPINGLABEL_UPLOAD_SUMMARY ?></a>
		</li>
	</ul>
</div>