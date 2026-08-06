<?php
// Requires variables: $ay (array or null)
$name = $ay['name'] ?? '';
$status = $ay['status'] ?? 'preparation';
?>

<div class="space-y-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Academic Year Name</label>
        <div class="mt-1">
            <input type="text" name="name" id="name" required placeholder="e.g. 2024-2025" value="<?php echo htmlspecialchars($name); ?>" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
        </div>
        <p class="mt-2 text-sm text-gray-500">The standard format is YYYY-YYYY.</p>
    </div>

    <!-- Status is read-only here or only shown if editing -->
    <?php if ($ay): ?>
    <div>
        <label class="block text-sm font-medium text-gray-700">Current Status</label>
        <div class="mt-2">
            <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 border border-gray-200 capitalize">
                <?php echo htmlspecialchars($status); ?>
            </span>
        </div>
        <p class="mt-2 text-xs text-gray-500">Status must be managed from the main list view to ensure data integrity.</p>
    </div>
    <?php endif; ?>
</div>
