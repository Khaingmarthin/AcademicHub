<?php
require_once 'config/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Update faculties table
    $columnsToAdd = [
        "ADD COLUMN `faculty_code` VARCHAR(50) NULL AFTER `faculty_name`",
        "ADD COLUMN `faculty_type` VARCHAR(50) NULL AFTER `faculty_code`",
        "ADD COLUMN `vision` TEXT NULL AFTER `description`",
        "ADD COLUMN `mission` TEXT NULL AFTER `vision`",
        "ADD COLUMN `status` VARCHAR(20) DEFAULT 'Active' AFTER `mission`"
    ];

    foreach ($columnsToAdd as $col) {
        try {
            $pdo->exec("ALTER TABLE `faculties` " . $col);
            echo "Successfully executed: ALTER TABLE faculties $col\n";
        } catch (PDOException $e) {
            echo "Skipped (already exists?): " . $e->getMessage() . "\n";
        }
    }

    // 2. Update courses table
    $courseCols = [
        "ADD COLUMN `major` VARCHAR(100) NULL AFTER `course_name`",
        "ADD COLUMN `year_level` VARCHAR(50) NULL AFTER `major`",
        "ADD COLUMN `semester` VARCHAR(50) NULL AFTER `year_level`",
        "ADD COLUMN `credits` INT DEFAULT NULL AFTER `semester`",
        "ADD COLUMN `status` VARCHAR(20) DEFAULT 'Active' AFTER `credits`"
    ];

    foreach ($courseCols as $col) {
        try {
            $pdo->exec("ALTER TABLE `courses` " . $col);
            echo "Successfully executed: ALTER TABLE courses $col\n";
        } catch (PDOException $e) {
            echo "Skipped (already exists?): " . $e->getMessage() . "\n";
        }
    }

    // 3. Clear existing faculties to insert the new detailed ones.
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE faculties");
    
    // Insert predefined faculties
    $facultiesData = [
        ['Faculty of Computer Science (FCS)', 'FCS', 'Academic Faculty', 'The Faculty of Computer Science is dedicated to advancing computer science education and research that creates real-world impact. It develops future IT professionals, innovators and leaders.', 'Provide quality education, research, innovation, practical learning, internships and modern computing education.', '', 'Active'],
        ['Faculty of Information Science (FIS)', 'FIS', 'Academic Faculty', 'Produce qualified computer scientists through student-centered education emphasizing practical learning, tutorials, projects and professional skills.', 'Produce qualified computer scientists through student-centered education emphasizing practical learning, tutorials, projects and professional skills.', '', 'Active'],
        ['Faculty of Computer Science and Technology (FCST)', 'FCST', 'Academic Faculty', 'Quality Education for Change, Peace and Progress and Innovative Education for a Knowledge, Pioneering and Global Society.', 'Provide holistic education that contributes to sustainable national development.', '', 'Active'],
        ['Information Technology Support and Management (ITSM)', 'ITSM', 'Academic Faculty', 'Provide innovative education that prepares globally competitive graduates and supports sustainable development.', 'Provide innovative education that prepares globally competitive graduates and supports sustainable development.', '', 'Active'],
        ['Department of Natural Science (Physics)', 'PHYSICS', 'Department', '', '', '', 'Active'],
        ['Department of Natural Language (Myanmar & English)', 'LANGUAGE', 'Department', '', '', '', 'Active'],
        ['Faculty of Computing (Mathematics)', 'MATH', 'Department', '', '', '', 'Active'],
        ['Finance Department', 'FINANCE', 'Administrative Office', '', '', 'The Finance Department is responsible for managing the university\'s financial resources efficiently and transparently. It oversees budgeting, accounting, procurement, and financial administration to ensure the smooth operation of academic and administrative activities. The department supports students, faculty, and staff by maintaining sound financial practices and contributing to the sustainable development of the university.', 'Active'],
        ['Student Affairs Department', 'STUDENT_AFFAIRS', 'Administrative Office', '', '', 'The Student Affairs Department is committed to supporting students throughout their academic journey by promoting their personal, academic, and social development. The department manages student registration, welfare services, extracurricular activities, scholarships, and campus events while fostering a safe, inclusive, and disciplined learning environment. It serves as a bridge between students and the university administration to enhance the overall student experience.', 'Active'],
        ['Library', 'LIBRARY', 'Administrative Office', '', '', 'The University Library provides students, faculty members, and researchers with access to quality academic resources and learning facilities. It offers a wide collection of books, journals, reference materials, and digital resources to support teaching, learning, and research. The library encourages lifelong learning, independent study, and academic excellence by creating a quiet, resourceful, and welcoming environment for the university community.', 'Active'],
        ['Administration Department', 'ADMIN', 'Administrative Office', '', '', 'The Administration Department is responsible for ensuring the efficient operation and effective management of the university\'s administrative services. It supports teaching, research, and student affairs by providing quality administrative assistance, maintaining transparent policies, and coordinating essential university operations. Through a service-oriented approach, the department works closely with students, faculty, staff, parents, and other stakeholders to create a well-organized, supportive, and productive academic environment.', 'Active']
    ];

    $stmt = $pdo->prepare("INSERT INTO faculties (faculty_name, faculty_code, faculty_type, vision, mission, description, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($facultiesData as $f) {
        $stmt->execute($f);
    }
    echo "Inserted predefined faculties.\n";

    // Courses Pre-population
    $pdo->exec("TRUNCATE TABLE courses");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    $stmt = $pdo->query("SELECT id FROM faculties WHERE faculty_code = 'FCS'");
    $fcs_id = $stmt->fetchColumn() ?: 1;

    $stmt = $pdo->query("SELECT id FROM faculties WHERE faculty_code = 'FCST'");
    $fcst_id = $stmt->fetchColumn() ?: 1;

    $stmt = $pdo->query("SELECT id FROM faculties WHERE faculty_code = 'LANGUAGE'");
    $lang_id = $stmt->fetchColumn() ?: 1;
    
    $stmt = $pdo->query("SELECT id FROM faculties WHERE faculty_code = 'PHYSICS'");
    $phys_id = $stmt->fetchColumn() ?: 1;
    
    $stmt = $pdo->query("SELECT id FROM faculties WHERE faculty_code = 'MATH'");
    $math_id = $stmt->fetchColumn() ?: 1;

    $coursesData = [
        // FIRST YEAR
        [$lang_id, 'Myanmar', 'M-1201', 'First Year', '', 'First Semester', 3],
        [$lang_id, 'English Proficiency II', 'E-1201', 'First Year', '', 'First Semester', 3],
        [$phys_id, 'Physics', 'P-1201', 'First Year', '', 'First Semester', 3],
        [$math_id, 'Discrete Mathematics', 'CST-1241', 'First Year', '', 'First Semester', 3],
        [$fcs_id, 'Programming Logic and Design(Programming in C++)', 'CST-1212', 'First Year', '', 'First Semester', 4],
        [$fcs_id, 'Database Fundamentals', 'CST-1223', 'First Year', '', 'First Semester', 4],
        [$fcst_id, 'Digital and Logic Design', 'CST-1234', 'First Year', '', 'First Semester', 4],
        
        // SECOND YEAR - Computer Science
        [$lang_id, 'English Proficiency IV', 'E-2201', 'Second Year', 'Computer Science', 'First Semester', 3],
        [$math_id, 'Differential Equations and Numerical Analysis', 'CST-2241', 'Second Year', 'Computer Science', 'First Semester', 3],
        [$fcs_id, 'Artificial Intelligence', 'CST-2212', 'Second Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Operating Systems', 'CST-2213', 'Second Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Software Analysis and Design', 'CST-2224', 'Second Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Data Communication and Networking', 'CST-2235', 'Second Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Web Technology(JavaScript)', 'CS-2256', 'Second Year', 'Computer Science', 'First Semester', 4],

        // SECOND YEAR - Computer Technology
        [$lang_id, 'English Proficiency IV (CT)', 'E-2201 (CT)', 'Second Year', 'Computer Technology', 'First Semester', 3],
        [$math_id, 'Differential Equations and Numerical Analysis (CT)', 'CST-2241 (CT)', 'Second Year', 'Computer Technology', 'First Semester', 3],
        [$fcs_id, 'Artificial Intelligence (CT)', 'CST-2212 (CT)', 'Second Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, 'Operating Systems (CT)', 'CST-2213 (CT)', 'Second Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, 'Software Analysis and Design (CT)', 'CST-2224 (CT)', 'Second Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, 'Data Communication and Networking (CT)', 'CST-2235 (CT)', 'Second Year', 'Computer Technology', 'First Semester', 4],
        [$fcst_id, 'Circuits and Electronics', 'CT-2236', 'Second Year', 'Computer Technology', 'First Semester', 4],
        
        // THIRD YEAR - Computer Science
        [$fcs_id, 'Operating Systems (3rd Yr)', 'CST-3211', 'Third Year', 'Computer Science', 'First Semester', 4],
        [$math_id, 'Probability and Statistics', 'CST-3242', 'Third Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Professional Ethics', 'CST-3213', 'Third Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Software Quality Assurance and Testing', 'CS-3224', 'Third Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Computer Networks I', 'CST-3235 (3rd Yr)', 'Third Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Human Computer Interaction', 'CST-3256', 'Third Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, '(Supporting Skill IV) Applied Database and Application(ADO.Net, C#)', 'CST-3257(SS)', 'Third Year', 'Computer Science', 'First Semester', 4],

        // THIRD YEAR - Computer Technology
        [$fcs_id, 'Operating Systems (3rd Yr CT)', 'CST-3211 (CT)', 'Third Year', 'Computer Technology', 'First Semester', 4],
        [$math_id, 'Probability and Statistics (CT)', 'CST-3242 (CT)', 'Third Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, 'Professional Ethics (CT)', 'CST-3213 (CT)', 'Third Year', 'Computer Technology', 'First Semester', 4],
        [$fcst_id, 'Computer Architecture and Organization II', 'CT-3234', 'Third Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, 'Computer Networks I (CT)', 'CST-3235 (CT 3rd Yr)', 'Third Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, 'Human Computer Interaction (CT)', 'CST-3256 (CT)', 'Third Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, '(Supporting Skill IV) Applied Database and Application(ADO.Net, C#) (CT)', 'CST-3257(SS) (CT)', 'Third Year', 'Computer Technology', 'First Semester', 4],
        
        // FOURTH YEAR - Computer Science
        [$fcs_id, 'Distributed and Parallel Computing', 'CST-4211', 'Fourth Year', 'Computer Science', 'First Semester', 4],
        [$math_id, 'Modeling and Simulations', 'CST-4242', 'Fourth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Object-Oriented Design and Development', 'CS-4223', 'Fourth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Advanced Artificial Intelligence(Knowledge representation and Management, Machine Learning with Python)', 'CS-4214(Elective I A)', 'Fourth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Advanced Database System', 'CS-4225(Elective II A)', 'Fourth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Strategic for Emerging Technologies(Cloud Computing)', 'CS-4216(Elective III A)', 'Fourth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Business Information System (Digital Business and e-Commence)', 'CST-4257(Elective IV A)', 'Fourth Year', 'Computer Science', 'First Semester', 4],
        
        // FOURTH YEAR - Computer Technology
        [$fcs_id, 'Distributed and Parallel Computing (CT)', 'CST-4211 (CT)', 'Fourth Year', 'Computer Technology', 'First Semester', 4],
        [$math_id, 'Modeling and Simulations (CT)', 'CST-4242 (CT)', 'Fourth Year', 'Computer Technology', 'First Semester', 4],
        [$fcst_id, 'Cryptography and Network Security', 'CT-4233', 'Fourth Year', 'Computer Technology', 'First Semester', 4],
        [$fcst_id, 'Embedded Systems Integrating IoT', 'CT-4234', 'Fourth Year', 'Computer Technology', 'First Semester', 4],
        [$fcst_id, 'Signals and Systems', 'CT-4235(Elective I)', 'Fourth Year', 'Computer Technology', 'First Semester', 4],
        [$fcst_id, 'Cyber  Security and Ethical Hacking', 'CT-4236(Elective II)', 'Fourth Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, 'Business Information System (Digital Business and e-Commence) (CT)', 'CST-4257(Elective III) (CT)', 'Fourth Year', 'Computer Technology', 'First Semester', 4],

        // FIFTH YEAR - Computer Science
        [$lang_id, 'Communication in Business English', 'E-5101', 'Fifth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Cyber Security and Digital Forensics', 'CS-5121', 'Fifth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Natural Language Processing', 'CS-5112(Elective I B)', 'Fifth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Data Science Fundamental', 'CST-5123 A(Elective II B)', 'Fifth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Data Analytic and Mining', 'CST-5123 B(Elective II B)', 'Fifth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Strategies for Emerging Technology(Virtualization, Blockchain, Cloud Security and internet of Things)', 'CS-5114(Elective III B)', 'Fifth Year', 'Computer Science', 'First Semester', 4],
        [$fcs_id, 'Business Information System(Enterprise Resource Planning(ERP))', 'CST-5155(Elective IV B)', 'Fifth Year', 'Computer Science', 'First Semester', 4],

        // FIFTH YEAR - Computer Technology
        [$lang_id, 'Communication in Business English (CT)', 'E-5101 (CT)', 'Fifth Year', 'Computer Technology', 'First Semester', 4],
        [$fcst_id, 'Digital Forensics', 'CT-5131', 'Fifth Year', 'Computer Technology', 'First Semester', 4],
        [$fcst_id, 'Advanced Networking', 'CT-5132', 'Fifth Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, 'Data Science Fundamental (CT)', 'CST-5123 A(Elective) (CT)', 'Fifth Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, 'Data Analytic and Mining (CT)', 'CST-5123 B(Elective) (CT)', 'Fifth Year', 'Computer Technology', 'First Semester', 4],
        [$fcst_id, 'Digital Signal Processing', 'CT-5134(Elective)', 'Fifth Year', 'Computer Technology', 'First Semester', 4],
        [$fcs_id, 'Business Information System(Enterprise Resource Planning(ERP)) (CT)', 'CST-5155(Elective) (CT)', 'Fifth Year', 'Computer Technology', 'First Semester', 4],
        [$fcst_id, 'Image Processing and Computer Vision', 'CT-5136(Elective)', 'Fifth Year', 'Computer Technology', 'First Semester', 4],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO courses (faculty_id, course_name, course_code, year_level, major, semester, credits, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
    foreach ($coursesData as $c) {
        try {
            $stmt->execute($c);
        } catch (PDOException $e) {
            echo "Skipped course " . $c[2] . " : " . $e->getMessage() . "\n";
        }
    }
    echo "Inserted predefined courses.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
