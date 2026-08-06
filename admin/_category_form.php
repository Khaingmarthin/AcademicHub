<?php
// Requires variables: $cat (array or null)
$name = $cat['name'] ?? '';
$description = $cat['description'] ?? '';
?>

<div class="space-y-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
        <div class="mt-1">
            <input type="text" name="name" id="name" required placeholder="e.g. Exam, Event, General" value="<?php echo htmlspecialchars($name); ?>" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <div class="mt-1">
            <textarea name="description" id="description" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-gray-300 rounded-md py-2 px-3"><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <p class="mt-2 text-sm text-gray-500">Briefly describe the purpose of this category.</p>
    </div>
</div>
