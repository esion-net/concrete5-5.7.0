<?
defined('C5_EXECUTE') or die("Access Denied.");
?>

<script type="text/template" class="attribute">
	<div class="form-group <% if (pending) { %>ccm-page-attribute-adding<% } %>" data-attribute-key-id="<%=akID%>">
		<a href="javascript:void(0)" data-remove-attribute-key="<%=akID%>"><i class="fa fa-minus-circle"></i></a>
		<label class="control-label"><%=label%></label>
		<div>
			<%=content%>
		</div>
		<input type="hidden" name="selectedAKIDs[]" value="<%=akID%>" />
	</div>
</script>

<div id="ccm-detail-page-attributes">

<section class="ccm-ui">
	<form method="post" action="<?=$controller->action('submit')?>" data-dialog-form="attributes" data-panel-detail-form="attributes">

        <? if (isset($sitemap) && $sitemap) { ?>
            <input type="hidden" name="sitemap" value="1" />
        <? } ?>

		<?=Loader::helper('concrete/ui/help')->notify('panel', '/page/attributes')?>
		<? if ($assignment->allowEditName()) { ?>
		<div class="form-group">
			<label for="cName" class="control-label"><?=t('Name')?></label>
			<div>
			<input type="text" class="form-control" id="cName" name="cName" value="<?=htmlentities( $c->getCollectionName(), ENT_QUOTES, APP_CHARSET) ?>" />
			</div>
		</div>
		<? } ?>

		<? if ($assignment->allowEditDateTime()) { ?>
		<div class="form-group">
			<label for="cName" class="control-label"><?=t('Created Time')?></label>
			<div>
				<? print $dt->datetime('cDatePublic', $c->getCollectionDatePublic()); ?>
			</div>
		</div>
		<? } ?>
		
		<? if ($assignment->allowEditUserID()) { ?>
		<div class="form-group">
			<label for="cName" class="control-label"><?=t('Author')?></label>
			<div>
			<? 
			print $uh->selectUser('uID', $c->getCollectionUserID());
			?>
			</div>
		</div>
		<? } ?>
		

		<? if ($assignment->allowEditDescription()) { ?>
		<div class="form-group">
			<label for="cDescription" class="control-label"><?=t('Description')?></label>
			<div>
				<textarea id="cDescription" name="cDescription" class="form-control" rows="8"><?=$c->getCollectionDescription()?></textarea>
			</div>
		</div>
		<? } ?>

	</form>
	<div class="ccm-panel-detail-form-actions dialog-buttons">
		<button class="pull-right btn btn-success" type="button" data-dialog-action="submit" data-panel-detail-action="submit"><?=t('Save Changes')?></button>
	</div>

</section>
</div>

<script type="text/javascript">

var renderAttribute = _.template(
    $('script.attribute').html()
);



ConcretePageAttributesDetail = {

	removeAttributeKey: function(akID) {
		var $attribute = $('div[data-attribute-key-id=' + akID + ']');
		$attribute.queue(function() {
			$(this).addClass('ccm-page-attribute-removing');
			ConcreteMenuPageAttributes.deselectAttributeKey(akID);
			$(this).dequeue();
		}).delay(400).queue(function() {
			$(this).remove();
			$(this).dequeue();
		});
	},

	addAttributeKey: function(akID) {
		jQuery.fn.dialog.showLoader();
		$.ajax({
			url: '<?=$controller->action("add_attribute")?>',
			dataType: 'json',
			data: {
				'akID': akID
			},
			type: 'post',
			success: function(r) {
                _.each(r.assets.css, function(css) {
                    ccm_addHeaderItem(css, 'CSS');
                });
                _.each(r.assets.javascript, function(javascript) {
                    ccm_addHeaderItem(javascript, 'JAVASCRIPT');
                });

				var $form = $('form[data-panel-detail-form=attributes]');
				$form.append(
					renderAttribute(r)
				);
				$form.delay(1).queue(function() {
					$('[data-attribute-key-id=' + r.akID + ']').removeClass('ccm-page-attribute-adding');
					$(this).dequeue();
				});
			},
			complete: function() {
				jQuery.fn.dialog.hideLoader();
			}
		});
	}
}

$(function() {

	var $form = $('form[data-panel-detail-form=attributes]');
	var selectedAttributes = <?=$selectedAttributes?>;
	_.each(selectedAttributes, function(attribute) {
		$form.append(renderAttribute(attribute));
	});
	$form.on('click', 'a[data-remove-attribute-key]', function() {
		var akID = $(this).attr('data-remove-attribute-key');
		ConcretePageAttributesDetail.removeAttributeKey(akID);
	});

    $(function() {
        ConcreteEvent.unsubscribe('AjaxFormSubmitSuccess.saveAttributes');
        ConcreteEvent.subscribe('AjaxFormSubmitSuccess.saveAttributes', function(e, data) {
            if (data.form == 'attributes') {
                ConcreteEvent.publish('SitemapUpdatePageRequestComplete', {'cID': data.response.cID});
            }
        });
    });

});

</script>