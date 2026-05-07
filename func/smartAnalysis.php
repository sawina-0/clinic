<?php
function analyzeSymptoms($text, $pdo) {
    $stopWords = ['и', 'в', 'на', 'у', 'меня', 'мне', 'я', 'это', 'то', 'так', 'же', 'или', 'но', 'а', 'за', 'по', 'из', 'от', 'до', 'со', 'при', 'к', 'с', 'без', 'под', 'над', 'о', 'об', 'про', 'через', 'для', 'как', 'что', 'если', 'бы', 'ещё', 'всё', 'все', 'очень', 'сильно', 'немного', 'чуть', 'совсем', 'есть', 'быть', 'стал', 'начала', 'начал', 'появился', 'появилась', 'появилось'];
    
    $originalText = mb_strtolower($text, 'UTF-8');
    $textForWords = $originalText;
    
    // Получаем все правила из БД
    $stmt = $pdo->query("SELECT keyword, direction_id, priority FROM symptoms");
    $allRules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Разделяем на фразы (с пробелами) и отдельные слова
    $phrases = [];
    $singleWords = [];
    foreach ($allRules as $rule) {
        if (strpos($rule['keyword'], ' ') !== false) {
            $phrases[] = $rule;
        } else {
            $singleWords[] = $rule;
        }
    }
    
    // Сортируем фразы по длине (от длинных к коротким)
    usort($phrases, function($a, $b) {
        return mb_strlen($b['keyword']) - mb_strlen($a['keyword']);
    });
    
    $directionScores = [];
    
    // 1. Ищем фразы целиком (точное вхождение)
    foreach ($phrases as $phrase) {
        $keyword = $phrase['keyword'];
        if (strpos($originalText, $keyword) !== false) {
            $dirId = $phrase['direction_id'];
            $priority = $phrase['priority'];
            $directionScores[$dirId] = ($directionScores[$dirId] ?? 0) + $priority;
            // Удаляем найденную фразу из текста для поиска отдельных слов
            $textForWords = str_replace($keyword, '', $textForWords);
        }
    }
    
    // 2. Разбиваем оставшийся текст на отдельные слова
    preg_match_all('/[а-яё0-9]+/u', $textForWords, $matches);
    $words = $matches[0];
    
    // Удаляем стоп-слова и короткие слова
    $words = array_filter($words, function($word) use ($stopWords) {
        return mb_strlen($word, 'UTF-8') >= 2 && !in_array($word, $stopWords);
    });
    $words = array_unique($words);
    
    $directionScoresWords = [];
    
    // 3. Ищем отдельные слова (с учётом опечаток)
    foreach ($words as $word) {
        $bestKeyword = null;
        $bestDistance = 2;
        
        foreach ($singleWords as $rule) {
            $keyword = $rule['keyword'];
            
            // Точное совпадение
            if ($word === $keyword) {
                $bestKeyword = $keyword;
                break;
            }
            
            // Нечёткое совпадение
            if (mb_strlen($word, 'UTF-8') >= 3) {
                $distance = levenshtein($word, $keyword);
                if ($distance <= $bestDistance) {
                    $bestDistance = $distance;
                    $bestKeyword = $keyword;
                }
            }
        }
        
        // Добавляем очки за найденное слово
        if ($bestKeyword) {
            foreach ($singleWords as $rule) {
                if ($rule['keyword'] === $bestKeyword) {
                    $dirId = $rule['direction_id'];
                    $priority = $rule['priority'];
                    $directionScoresWords[$dirId] = ($directionScoresWords[$dirId] ?? 0) + $priority;
                }
            }
        }
    }
    
    // Объединяем очки от фраз и от отдельных слов
    foreach ($directionScoresWords as $dirId => $score) {
        $directionScores[$dirId] = ($directionScores[$dirId] ?? 0) + $score;
    }
    
    if (empty($directionScores)) {
        return 1; // Терапия
    }
    
    arsort($directionScores);
    return key($directionScores);
}

function getDoctorsByDirection($direction_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            d.doctor_id,
            d.exp,
            u.photo,
            u.surname,
            u.name,
            u.sec_name,
            dir.name as direction_name,
            dir.specialist_name
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        JOIN directions dir ON d.direction_id = dir.direction_id
        WHERE d.direction_id = ?
        ORDER BY d.exp DESC
    ");
    $stmt->execute([$direction_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>