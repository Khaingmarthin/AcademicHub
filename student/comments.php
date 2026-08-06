<?php
// This component expects $announcement_id to be set by the including file.
if (!isset($announcement_id)) {
    return;
}

$is_student = isset($_SESSION['role']) && $_SESSION['role'] === 'student';
$user_id = $_SESSION['user_id'] ?? 0;
$csrf_token = generate_csrf_token();
?>

<div class="mt-8 border-t border-gray-200 pt-8" id="comments-section">
    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
        <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        Discussion
        <span id="comment-count" class="ml-3 bg-gray-100 text-gray-600 text-sm py-1 px-3 rounded-full">0</span>
    </h3>

    <!-- Post new comment form -->
    <?php if ($is_student): ?>
        <div class="bg-gray-50 rounded-lg p-4 mb-8 border border-gray-100 shadow-sm">
            <form id="new-comment-form">
                <input type="hidden" name="announcement_id" value="<?php echo (int)$announcement_id; ?>">
                <input type="hidden" name="parent_id" value="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-3">
                    <label for="comment-content" class="sr-only">Your comment</label>
                    <textarea id="comment-content" name="content" rows="3" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-3" placeholder="Join the discussion..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" id="submit-comment" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Post Comment
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8 rounded-r-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Please <a href="../auth/login.php" class="font-medium underline hover:text-blue-600">log in as a student</a> to participate in the discussion.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Comments Container -->
    <div id="comments-container" class="space-y-6">
        <div class="text-center py-8">
            <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <p class="mt-2 text-sm text-gray-500">Loading comments...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const announcementId = <?php echo (int)$announcement_id; ?>;
    const isStudent = <?php echo $is_student ? 'true' : 'false'; ?>;
    const currentUserId = <?php echo $user_id; ?>;
    const csrfToken = '<?php echo $csrf_token; ?>';
    
    // Fetch and render comments
    function fetchComments() {
        fetch(`../ajax/fetch_comments.php?announcement_id=${announcementId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderComments(data.comments, data.total);
                } else {
                    document.getElementById('comments-container').innerHTML = `<p class="text-red-500">Error loading comments: ${data.message}</p>`;
                }
            })
            .catch(err => {
                document.getElementById('comments-container').innerHTML = `<p class="text-red-500">Failed to load comments.</p>`;
            });
    }

    // Recursively render comment threads
    function renderComments(comments, total) {
        document.getElementById('comment-count').textContent = total;
        
        const container = document.getElementById('comments-container');
        if (comments.length === 0) {
            container.innerHTML = `<div class="text-center py-8 text-gray-500"><svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg><p>No comments yet. Be the first to share your thoughts!</p></div>`;
            return;
        }

        let html = '';
        comments.forEach(c => {
            html += buildCommentHTML(c, 0);
        });
        container.innerHTML = html;

        // Attach reply listeners
        if (isStudent) {
            document.querySelectorAll('.reply-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parentId = this.dataset.id;
                    showReplyForm(parentId);
                });
            });
        }
    }

    function buildCommentHTML(comment, depth) {
        const date = new Date(comment.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
        const indentClass = depth > 0 ? `ml-${Math.min(depth * 8, 16)} pl-4 border-l-2 border-gray-100` : '';
        const bgClass = depth > 0 ? 'bg-gray-50/50' : 'bg-white';
        
        let html = `
        <div class="flex space-x-3 ${indentClass} mb-6 mt-4" id="comment-${comment.id}">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm uppercase">
                    ${comment.author_name.charAt(0)}
                </div>
            </div>
            <div class="flex-grow ${bgClass} rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-bold text-gray-900">${comment.author_name}</h4>
                    <span class="text-xs text-gray-500">${date}</span>
                </div>
                <div class="text-sm text-gray-700 whitespace-pre-line mb-3">${comment.content}</div>
                
                <div class="flex items-center text-xs space-x-4">
                    ${isStudent ? `<button class="reply-btn text-gray-500 hover:text-blue-600 font-medium flex items-center" data-id="${comment.id}"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg> Reply</button>` : ''}
                </div>
                <div id="reply-container-${comment.id}" class="mt-3"></div>
            </div>
        </div>`;

        if (comment.children && comment.children.length > 0) {
            comment.children.forEach(child => {
                html += buildCommentHTML(child, depth + 1);
            });
        }
        return html;
    }

    function showReplyForm(parentId) {
        // Remove existing reply forms
        document.querySelectorAll('.inline-reply-form').forEach(el => el.remove());

        const formHtml = `
        <form class="inline-reply-form mt-4" onsubmit="submitReply(event, ${parentId})">
            <div class="mb-2">
                <textarea id="reply-content-${parentId}" rows="2" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" placeholder="Write a reply..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="this.closest('form').remove()" class="px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">Post Reply</button>
            </div>
        </form>`;
        
        const container = document.getElementById(`reply-container-${parentId}`);
        container.innerHTML = formHtml;
        document.getElementById(`reply-content-${parentId}`).focus();
    }

    // Expose submitReply to window since it's called from inline HTML
    window.submitReply = function(e, parentId) {
        e.preventDefault();
        const content = document.getElementById(`reply-content-${parentId}`).value;
        postComment(content, parentId);
    };

    if (isStudent) {
        document.getElementById('new-comment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const content = document.getElementById('comment-content').value;
            postComment(content, null);
        });
    }

    function postComment(content, parentId) {
        const formData = new FormData();
        formData.append('announcement_id', announcementId);
        formData.append('content', content);
        formData.append('csrf_token', csrfToken);
        if (parentId) {
            formData.append('parent_id', parentId);
        }

        fetch('../ajax/post_comment.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (!parentId) {
                    document.getElementById('comment-content').value = '';
                }
                fetchComments(); // Refresh comments list
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            alert('Failed to post comment.');
        });
    }

    // Initial load
    fetchComments();
});
</script>
