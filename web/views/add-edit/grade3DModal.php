<!-- grade3DModal -->
<div class="modal fade" id="grade3DModal" tabindex="-1" role="dialog" aria-labelledby="grade3DModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="grade3DModalLabel">Выбрать оценку 3Д</h4>
      </div>
      <div class="modal-body">
        <select class="form-control add3DGrade">
		<?php $tmpWorkName = ''; ?>
      <?php foreach ( $gradingSystem3D??[] as $gs3DRow ): ?>
	  
	  <?php // Выведем черту после каждого типа оценок. Для лучшего восприятия ?>
		<?php if ( $gs3DRow['work_name'] !== $tmpWorkName ):  ?>
			<?php $tmpWorkName = $gs3DRow['work_name']; ?>
			<option value="">---</option>
		<?php endif; ?>
	  
        <option data-workName="<?=$gs3DRow['work_name']?>" data-points="<?=$gs3DRow['points']?>" value="<?=$gs3DRow['id']?>" title="<?=$gs3DRow['description']?>" >
          <?= $gs3DRow['work_name'] . " - " . (((float)$gs3DRow['points'] > 0.00) ? $gs3DRow['points'] : "(Индивид.)") ?>
          <?php
            $descr = $gs3DRow['description'];
            if ( mb_strlen( $descr, "UTF-8" ) > 50 ) $descr = mb_substr($gs3DRow['description'], 0, 50, "UTF-8") . "...";
          ?>
          <?=": ".$descr?>
        </option>
      <?php endforeach; ?>
      </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
      </div>
    </div>
  </div>
</div>

<!-- Repairs Prices Modal -->
<div class="modal fade" id="repairPricesModal" tabindex="-1" role="dialog" aria-labelledby="repairPricesModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title" id="repairPricesModalLabel"><b><i class="fab fa-linode"></i></b> <span class="titleText">Выбрать оценку ремонта 3Д модели</span></h4>
            </div>
            <div class="modal-body">
                <select class="form-control selectRepairPrice">
                    <option value="">---</option>
                    <?php foreach ( $gradingSystem3DRep??[] as $gs3DRepRow ): ?>
                        <?php if ( $gs3DRepRow['grade_type'] != 8 ) continue; ?>
                        <option data-workName="<?=$gs3DRepRow['work_name']?>" data-points="<?=$gs3DRepRow['points']?>" data-gradeType="<?=$gs3DRepRow['grade_type']?>" value="<?=$gs3DRepRow['id']?>" title="<?=$gs3DRepRow['description']?>" >
                            <?= $gs3DRepRow['work_name'] . " - ". $gs3DRepRow['points']?>
                            <?php
                            $descr = $gs3DRepRow['description'];
                            if ( mb_strlen( $descr, "UTF-8" ) > 50 ) $descr = mb_substr($gs3DRepRow['description'], 0, 50, "UTF-8") . "...";
                            ?>
                            <?= ": " . $descr ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select class="form-control selectMMRepairPrice hidden">
                    <option value="" selected >---</option>
                    <option data-workName="Ремонт ММ" data-points="0" data-gradeType="9" value="100" title="Ремонт мастер модели" >
                        Ремонт ММ - индивидуально
                    </option>
                    <option data-workName="Ремонт производства" data-points="0" data-gradeType="10" value="101" title="Производственный ремонт" >
                        Производственный ремонт - индивидуально
                    </option>
                </select>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>

<table class="hidden">
    <tr class="gs_proto3DRow">
        <td style="width: 30px"></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="width:100px;">
            <button class="btn btn-sm btn-default ma3DgsDell" type="button" title="Удалить Оценку">
                <span class="glyphicon glyphicon-trash"></span>
            </button>
        </td>
    </tr>
    <tr class="gs_protoMMRow">
        <td style="width: 30px"></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="width:100px;">
            <button class="btn btn-sm btn-default ma3DgsDell" type="button" title="Удалить Оценку">
                <span class="glyphicon glyphicon-trash"></span>
            </button>
        </td>
    </tr>
</table>