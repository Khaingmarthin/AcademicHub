<?php
// Requires variables: $announcement (array or null), $categories (array), $academic_years (array)
$title = $announcement['title'] ?? '';
$content = $announcement['content'] ?? '';
$category_id = $announcement['category_id'] ?? '';
$academic_year_id = $announcement['academic_year_id'] ?? ($_SESSION['current_academic_year_id'] ?? '');
$publish_date = !empty($announcement['publish_date']) ? date('Y-m-d\TH:i:s', strtotime($announcement['publish_date'])) : date('Y-m-d\TH:i:s');
$is_urgent = $announcement['is_urgent'] ?? 0;

$attachment_path = $announcement['attachment_path'] ?? '';
?>

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6">
        <div class="md:col-span-2">
            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" name="title" id="title" required value="<?php echo htmlspecialchars($title); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
        </div>

        <div class="md:col-span-2">
            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
            <select name="category_id" id="category_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">Select a category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="publish_date" class="block text-sm font-medium text-gray-700">Publish Date</label>
            <input type="datetime-local" step="1" name="publish_date" id="publish_date" value="<?php echo htmlspecialchars($publish_date); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
        </div>
    </div>

    <div>
        <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
        <textarea id="content" name="content" rows="8" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border"><?php echo htmlspecialchars($content); ?></textarea>
    </div>
    
    <div>
        <label for="attachment" class="block text-sm font-medium text-gray-700">Attachment (Optional)</label>
        <?php if ($attachment_path): ?>
            <div class="mb-2 text-sm text-gray-600">Current file: <a href="/<?php echo htmlspecialchars($attachment_path); ?>" target="_blank" class="text-blue-600 hover:underline">View File</a></div>
        <?php endif; ?>
        <input type="file" name="attachment" id="attachment" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
    </div>

    <div class="grid grid-cols-1 gap-4">
        <div class="flex items-center">
            <input id="is_urgent" name="is_urgent" type="checkbox" value="1" <?php echo $is_urgent ? 'checked' : ''; ?> class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
            <label for="is_urgent" class="ml-2 block text-sm text-gray-900 font-medium">Mark as Urgent</label>
        </div>
    </div>
</div>
