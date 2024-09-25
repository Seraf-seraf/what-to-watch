<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Сидер, который использовался для формирования документации
 */
class MockSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            DB::statement(
                "
                INSERT INTO films (id, imdb_id, name, posterImage, previewImage, backgroundImage, backgroundColor, videoLink, previewVideoLink, description, director, starring, runTime, genre, released, status)
                VALUES
                (65, 'tt0191807', 'Example Movie 1', 'https://example.com/poster1.jpg', 'https://example.com/preview1.jpg', 'https://example.com/background1.jpg', '#FFFFFF', 'https://example.com/video1.mp4', 'https://example.com/previewVideo1.mp4', 'A great movie about...', 'Director 1', '[\"Actor 1\", \"Actor 2\"]', 120, '[\"action\"]', 2021, 'ready'),
                (66, 'tt6351907', 'Example Movie 2', 'https://example.com/poster2.jpg', 'https://example.com/preview2.jpg', 'https://example.com/background2.jpg', '#ABCDEF', 'https://example.com/video2.mp4', 'https://example.com/previewVideo2.mp4', 'A biography of a famous person.', 'Director 2', '[\"Actor 3\", \"Actor 4\"]', 130, '[\"biography\"]', 2019, 'ready'),
                (67, 'tt9915202', 'Example Movie 3', 'https://example.com/poster3.jpg', 'https://example.com/preview3.jpg', 'https://example.com/background3.jpg', '#123456', 'https://example.com/video3.mp4', 'https://example.com/previewVideo3.mp4', 'An action-packed thriller...', 'Director 3', '[\"Actor 5\", \"Actor 6\"]', 150, '[\"thriller\"]', 2020, 'ready'),
                (68, 'tt5929525', 'Example Movie 4', 'https://example.com/poster4.jpg', 'https://example.com/preview4.jpg', 'https://example.com/background4.jpg', '#654321', 'https://example.com/video4.mp4', 'https://example.com/previewVideo4.mp4', 'A touching drama...', 'Director 4', '[\"Actor 7\", \"Actor 8\"]', 140, '[\"drama\"]', 2018, 'ready'),
                (69, 'tt9871837', 'Example Movie 5', 'https://example.com/poster5.jpg', 'https://example.com/preview5.jpg', 'https://example.com/background5.jpg', '#ABC123', 'https://example.com/video5.mp4', 'https://example.com/previewVideo5.mp4', 'A historical epic...', 'Director 5', '[\"Actor 9\", \"Actor 10\"]', 160, '[\"history\"]', 2022, 'ready');"
            );

            DB::statement("
                INSERT INTO users (id, email, password, name, file)
                VALUES
                (1, 'user1@example.com', 'hashed_password_1', 'User One', NULL),
                (2, 'user2@example.com', 'hashed_password_2', 'User Two', NULL),
                (3, 'user3@example.com', 'hashed_password_3', 'User Three', NULL),
                (4, 'user4@example.com', 'hashed_password_4', 'User Four', NULL),
                (5, 'user5@example.com', 'hashed_password_5', 'User Five', NULL);"
            );

            DB::statement('
                INSERT INTO promo (film_id) VALUES
                (65),
                (66),
                (67),
                (68);'
            );

            DB::statement('
                INSERT INTO favorites (user_id, film_id) VALUES
                (1, 65),
                (2, 66),
                (3, 67),
                (1, 68),
                (4, 69);'
            );

            DB::statement("
                INSERT INTO genres (name) VALUES
                ('Шутер'),
                ('Драма'),
                ('Семейный'),
                ('Комедия');"
            );

            DB::statement("
                INSERT INTO comments (id, user_id, text, rating, film_id, created_at)
                VALUES
                (1, 1, 'Отличный фильм!', 8, 65, NOW()),
                (2, 2, 'Неплохой, но есть и лучше.', 6, 66, NOW()),
                (3, 3, 'Не рекомендую, слишком скучно.', 3, 67, NOW()),
                (4, 1, 'Прекрасный сюжет и актеры!', 9, 68, NOW()),
                (5, 4, 'Хорошо снято, но длина утомляет.', 7, 69, NOW()),
                (6, 2, 'Это просто шедевр!', 10, 65, NOW()),
                (7, 3, 'Сюжет интересный, но предсказуемый.', 5, 66, NOW()),
                (8, 4, 'Ожидал большего от этого фильма.', 4, 67, NOW()),
                (9, 1, 'Фильм оставил двойственные чувства.', 6, 68, NOW()),
                (10, 5, 'Отличная работа, рекомендую!', 9, 69, NOW());"
            );

            DB::statement("
                INSERT INTO comments (user_id, text, film_id, parent_id, created_at)
                VALUES
                (2, 'Согласен, мне тоже очень понравился!', 65, 1, NOW()),
                (3, 'Не могу с этим согласиться.', 66, 2, NOW()),
                (1, 'Наверняка, вы просто не понимаете.', 67, 3, NOW()),
                (4, 'Согласен, игра актеров великолепна!', 68, 4, NOW()),
                (2, 'Действительно, могло быть и короче.', 69, 5, NOW());"
            );

            DB::statement("
                INSERT IGNORE INTO roles (id, name) VALUES (1, 'admin');"
            );
        });
    }
}
