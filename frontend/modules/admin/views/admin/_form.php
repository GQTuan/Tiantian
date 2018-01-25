<div class="row cl field-adminuser-pid">
    <label class="form-label col-sm-2" for="adminuser-pid">上级(<?= $name ?>)</label>
    <div class="formControls col-sm-9">
        <select id="adminuser-pid" class="input-text" name="AdminUser[pid]">
        <?php foreach ($arr as $key => $val): ?>
            <option value="<?= $key ?>"><?= $val ?></option>
        <?php endforeach ?>
        </select>
    </div>

    <div class="help-block"></div>
</div>