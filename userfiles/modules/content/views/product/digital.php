<?php
$digitalRand = 'digital_file_' . $data['id'] . '_' . uniqid();
$digitalFile = isset($contentData['digital_file']) ? $contentData['digital_file'] : '';
$digitalMaxDownloads = isset($contentData['digital_max_downloads']) ? $contentData['digital_max_downloads'] : '';
$digitalExpiresDays = isset($contentData['digital_expires_days']) ? $contentData['digital_expires_days'] : '';
?>

<div class="card-header no-border mt-3">
    <label class="form-label font-weight-bold"><?php _e("Digital product"); ?></label>
</div>

<div class="row py-0">
    <div class="col-md-12 ps-md-0">
        <div class="form-group">
            <label class="form-label"><?php _e("Digital file"); ?></label>
            <small class="text-muted d-block mb-3"><?php _e("Upload or select a file that will be delivered after a verified purchase."); ?></small>
            <div class="input-group mb-3">
                <input type="text"
                    class="form-control"
                    id="<?php echo $digitalRand; ?>"
                    name="content_data[digital_file]"
                    value="<?php echo $digitalFile; ?>"
                    readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="mw_select_digital_file_<?php echo $digitalRand; ?>()">
                    <?php _e("Select file"); ?>
                </button>
                <button class="btn btn-outline-danger" type="button" onclick="mw_clear_digital_file_<?php echo $digitalRand; ?>()">
                    <?php _e("Clear"); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row py-0">
    <div class="col-md-6 ps-md-0">
        <div class="form-group">
            <label class="form-label"><?php _e("Max downloads"); ?></label>
            <small class="text-muted d-block mb-3"><?php _e("Leave empty to use the default limit (unlimited)."); ?></small>
            <input type="number" min="0" class="form-control" name="content_data[digital_max_downloads]" value="<?php echo $digitalMaxDownloads; ?>">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label"><?php _e("Expires in (days)"); ?></label>
            <small class="text-muted d-block mb-3"><?php _e("Leave empty for no expiration."); ?></small>
            <input type="number" min="0" class="form-control" name="content_data[digital_expires_days]" value="<?php echo $digitalExpiresDays; ?>">
        </div>
    </div>
</div>

<script>
    mw.require('filepicker.js');

    function mw_select_digital_file_<?php echo $digitalRand; ?>() {
        var dialog;
        var picker = new mw.filePicker({
            label: false,
            autoSelect: false,
            footer: true,
            _frameMaxHeight: true,
            onResult: function (res) {
                var url = res && res.src ? res.src : res;
                if (!url) {
                    return;
                }
                url = url.toString();
                mw.$('#<?php echo $digitalRand; ?>').val(url).trigger('input');
                if (dialog) {
                    dialog.remove();
                }
            }
        });
        dialog = mw.top().dialog({
            content: picker.root,
            title: mw.lang('Select file'),
            footer: false,
            width: 860
        });
    }

    function mw_clear_digital_file_<?php echo $digitalRand; ?>() {
        mw.$('#<?php echo $digitalRand; ?>').val('').trigger('input');
    }
</script>
