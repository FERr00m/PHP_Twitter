<?php
if ($posts) {?>

<section class="wrapper">
    <ul class="tweet-list">
        <?php foreach($posts as $post): ?>
            <li>
                <article class="tweet">
                    <div class="row">
                        <img class="avatar" src="<?=get_url($post['avatar'])?>" alt="Аватар пользователя <?=$post['name']?>">
                        <div class="tweet__wrapper">
                            <header class="tweet__header">
                                <h3 class="tweet-author"><?=$post['name']?>
                                    <a href="<?=get_url('user_posts.php?id=' . $post['user_id'])?>" class="tweet-author__add tweet-author__nickname">@<?=$post['login']?></a>
                                    <time class="tweet-author__add tweet__date"><?=date('d.m.y в H:i', strtotime($post['date']))?></time>
                                </h3>
                                <button class="tweet__delete-button chest-icon"></button>
                            </header>
                            <div class="tweet-post">
                                <p class="tweet-post__text"><?=$post['text']?></p>
                                <? if($post['image']):?>
                                <figure class="tweet-post__image">
                                    <img src="<?=$post['image']?>" alt="picture">
                                </figure>
                                <?endif;?>
                            </div>
                        </div>
                    </div>
                    <footer>
                        <button class="tweet__like">0</button>
    <!--                    tweet__like_active-->
                    </footer>
                </article>
            </li>
        <?endforeach;?>
    </ul>
</section>
<?php } else {
    echo 'Постов нет';
}?>