<?php
require 'config/db.php';

$data = [
    'FCS' => [
        'description' => "The Faculty of Computer Science is dedicated to advancing computer science education and research that creates real-world impact. It aims to develop future IT professionals, innovators, and leaders while contributing to local technological development. The faculty provides high-quality education, combines theory with practical applications, encourages research and innovation, and promotes effective teaching and learning through ICT. Students are trained to become skilled problem solvers, logical thinkers, and collaborative team members through a curriculum covering programming, artificial intelligence, cybersecurity, software engineering, geographic information systems, and other modern computing fields. In addition to classroom learning, students gain practical experience through research, internships, and community engagement.",
        'vision' => "",
        'mission' => ""
    ],
    'FIS' => [
        'description' => "The Faculty of Information Science is committed to producing qualified computer scientists and IT professionals with strong academic knowledge and practical skills. The faculty follows a student-centered education system that emphasizes hands-on learning through practical sessions, tutorials, and project-based assessments. It also ensures the effective delivery of courses and practical training according to academic schedules, preparing students to meet the demands of the modern technology field.",
        'vision' => "",
        'mission' => ""
    ],
    'FCST' => [
        'description' => "",
        'vision' => "Quality Education for Change ,Peace and Progress and Innovative education for a knowledge, pioneering, and global society.",
        'mission' => "To provide a holistic and empowering education system that enables all students to realize and appreciate fully their inheritance and potential contributing to a peaceful and sustainable National Development."
    ],
    'ITSM' => [
        'description' => "The university is committed to providing quality and innovative education that promotes positive change, peace, and sustainable progress. It strives to build a knowledgeable, forward-thinking, and globally competitive community while empowering students to reach their full potential. Through a holistic education system, the university prepares graduates to contribute responsibly to national development and create a peaceful and sustainable society.",
        'vision' => "",
        'mission' => ""
    ],
    'PHYSICS' => [
        'description' => "The Department of Natural Science (Physics) is dedicated to providing quality education that connects theoretical knowledge with practical applications. It emphasizes hands-on learning through experiments, tutorials, and project-based activities to strengthen students' scientific understanding and problem-solving skills. The department aims to develop competent graduates with strong analytical abilities, practical experience, and the knowledge needed to contribute effectively to science, engineering, and technological advancement.",
        'vision' => "",
        'mission' => ""
    ],
    'LANGUAGE' => [
        'description' => "The Department of Natural Language (Myanmar and English) is committed to developing students' communication skills in both Myanmar and English while preparing them for academic and professional success. The department emphasizes English language proficiency through IELTS-based learning, helping students strengthen their reading, writing, listening, and speaking skills. It also enhances students' academic vocabulary and language accuracy, enabling them to pursue higher education, especially in IT and computing, and communicate effectively in a global environment.",
        'vision' => "",
        'mission' => ""
    ],
    'MATH' => [
        'description' => "The Faculty of Computing (Mathematics) is dedicated to providing a strong foundation in mathematics that supports computing, science, and technology. The faculty emphasizes logical thinking, analytical reasoning, and problem-solving skills through both theoretical knowledge and practical applications. It prepares students with the mathematical competence required for modern computing disciplines while encouraging critical thinking, innovation, and lifelong learning to contribute effectively to academic, professional, and technological development.",
        'vision' => "",
        'mission' => ""
    ],
    'FINANCE' => [
        'description' => "The Finance Department is responsible for managing the university's financial resources efficiently and transparently. It oversees budgeting, accounting, procurement, and financial administration to ensure the smooth operation of academic and administrative activities. The department supports students, faculty, and staff by maintaining sound financial practices and contributing to the sustainable development of the university.",
        'vision' => "",
        'mission' => ""
    ],
    'STUDENT_AFFAIRS' => [
        'description' => "The Student Affairs Department is committed to supporting students throughout their academic journey by promoting their personal, academic, and social development. The department manages student registration, welfare services, extracurricular activities, scholarships, and campus events while fostering a safe, inclusive, and disciplined learning environment. It serves as a bridge between students and the university administration to enhance the overall student experience.",
        'vision' => "",
        'mission' => ""
    ],
    'LIBRARY' => [
        'description' => "The University Library provides students, faculty members, and researchers with access to quality academic resources and learning facilities. It offers a wide collection of books, journals, reference materials, and digital resources to support teaching, learning, and research. The library encourages lifelong learning, independent study, and academic excellence by creating a quiet, resourceful, and welcoming environment for the university community.",
        'vision' => "",
        'mission' => ""
    ],
    'ADMIN' => [
        'description' => "The Administration Department is responsible for ensuring the efficient operation and effective management of the university's administrative services. It supports teaching, research, and student affairs by providing quality administrative assistance, maintaining transparent policies, and coordinating essential university operations. Through a service-oriented approach, the department works closely with students, faculty, staff, parents, and other stakeholders to create a well-organized, supportive, and productive academic environment.",
        'vision' => "",
        'mission' => ""
    ]
];

foreach ($data as $code => $info) {
    $stmt = $pdo->prepare("UPDATE faculties SET description = ?, vision = ?, mission = ? WHERE faculty_code = ?");
    $stmt->execute([$info['description'], $info['vision'], $info['mission'], $code]);
    echo "Updated $code\n";
}
echo "Done.";
?>
