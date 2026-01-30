<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการหนังสือ</title>
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
</head>

<body>
    <div class="container">
        <h1>รายการหนังสือ</h1>

        @if($books->isEmpty())
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <p>ยังไม่มีหนังสือ</p>
        </div>
        @else
        @foreach($books as $book)
        <div class="books-card">
            <div>
                <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->name }}">
            </div>
            
            <div class="books-info">
                <div class="label">ชื่อหนังสือ</div>
                <div class="value">{{ $book->name }}</div>
            </div>

            <div class="books-info">
                <div class="label">ผู้เขียน</div>
                <div class="value">{{ $book->author }}</div>
            </div>

            <div class="books-info">
                <div class="label">หมวดหมู่</div>
                <div class="value">{{ $book->category->name ?? '-' }}</div>
            </div>

            <div class="books-info">
                <div class="label">ราคา</div>
                <div class="value">{{ number_format($book->price, 2) }} บาท</div>
            </div>

            <div class="books-actions">
                <button class="btn-edit"
                    onclick="openEditModal(
                        '{{ $book->id }}',
                        '{{ $book->name }}',
                        '{{ $book->price }}',
                        '{{ $book->author }}',
                        '{{ $book->image }}',
                        '{{ $book->category_id }}'
                    )">
                    แก้ไข
                </button>

                <form action="{{ route('books.destroy', $book->id) }}" method="POST"
                    onsubmit="return confirm('แน่ใจว่าต้องการลบ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete">
                        ลบ
                    </button>
                </form>
            </div>
        </div>
        @endforeach
        @endif

        <button class="add-book-btn" onclick="openModal()">+ เพิ่มหนังสือ</button>
    </div>

    <!-- Modal -->
    <div id="bookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">เพิ่มหนังสือใหม่</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="bookForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="form-group">
                    <label for="name">ชื่อหนังสือ</label>
                    <input type="text" id="name" name="name" placeholder="กรอกชื่อหนังสือ" required>
                </div>

                <div class="form-group">
                    <label for="author">ผู้เขียน</label>
                    <input type="text" id="author" name="author" placeholder="กรอกผู้เขียน" required>
                </div>

                <div class="form-group">
                    <label for="category">หมวดหมู่</label>
                    @if($categories->count() > 0)
                    <select id="category" name="category_id" required>
                        <option value="">-- เลือกหมวดหมู่ --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="text" name="new_category" placeholder="ยังไม่มีหมวด กรุณาเพิ่มหมวดใหม่" required>
                    @endif
                </div>

                <div class="form-group">
                    <label for="price">ราคา (บาท)</label>
                    <input type="number" id="price" name="price" placeholder="กรอกราคา" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="image">รูปภาพ</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-group" id="oldImageBox" style="display:none;">
                    <label>รูปเดิม</label><br>
                    <img id="oldImage" src="" width="120" style="border-radius:8px;">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">ยกเลิก</button>
                    <button type="submit" class="btn-submit">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalTitle').innerText = 'เพิ่มหนังสือใหม่';
            document.getElementById('bookForm').action = '/books';
            document.getElementById('formMethod').value = 'POST';

            document.getElementById('name').value = '';
            document.getElementById('price').value = '';
            document.getElementById('author').value = '';
            document.getElementById('image').value = '';
            
            const categorySelect = document.querySelector('select[name="category_id"]');
            if (categorySelect) {
                categorySelect.value = '';
            }

            document.getElementById('oldImageBox').style.display = 'none';
            document.getElementById('bookModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('bookModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function openEditModal(id, name, price, author, image, categoryId) {
            document.getElementById('modalTitle').innerText = 'แก้ไขหนังสือ';
            document.getElementById('bookForm').action = '/books/' + id;
            document.getElementById('formMethod').value = 'PUT';

            document.getElementById('name').value = name;
            document.getElementById('price').value = price;
            document.getElementById('author').value = author;

            const categorySelect = document.querySelector('select[name="category_id"]');
            if (categorySelect) {
                categorySelect.value = categoryId;
            }

            document.getElementById('oldImage').src = '/storage/' + image;
            document.getElementById('oldImageBox').style.display = 'block';

            document.getElementById('bookModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

    </script>
</body>

</html>