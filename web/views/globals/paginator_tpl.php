<?php use soffit\HtmlHelper; ?>
<nav aria-label="Page navigation">
    <ul class="<?= $this->size ?> <?= $this->class ?>">
    <?php if ($this->pagination->currentPage > $this->squaresPerPage): ?>
        <li>
            <a href="<?=HtmlHelper::URL('/',['page'=>$this->backToPage])?>" aria-label="Prev" title="Назад на пред. <?= $this->backToPage ?>">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
    <?php endif; ?>

    <?php for( $i = $this->startFromPage; $i <= $this->pagination->countPages; $i++ ) : ?>
        <?php $active = ''; $link = HtmlHelper::URL('/',['page'=>$i]); ?>        
        <?php if ( $this->pagination->currentPage == $i ) {$active = 'active'; $link = "#";} ?>
            <li class="<?=$active?>" >
                <a href="<?=$link?>" aria-label="" title="" class="<?=$active?'disabled':''?>">
                    <?=$i?>
                    <?php if ($active): ?>
                        <span class="sr-only">(current)</span>
                    <?php endif;?>
                </a>
            </li>
        <?php if ( $i !== 0 ) $nn = $i / $this->squaresPerPage; ?>
        <?php if ( is_int($nn) && ( $i < $this->pagination->countPages ) ): ?>
            <?php $nextI = $i + 1; // определяем след. страницу на которую перейдем после клика ?>
            <li>
                <a href="<?=HtmlHelper::URL('/',['page'=>$nextI])?>" aria-label="Next" title="Вперед на след. <?= $this->squaresPerPage ?>">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
            <?php break; ?>
        <?php  endif; ?>
    <?php endfor; ?>
    </ul>
</nav>