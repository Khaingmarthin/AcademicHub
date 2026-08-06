CREATE TABLE `academic_year_levels` (
  `id` INT NOT NULL AUTO_INCREMENT, 
  `level_name` VARCHAR(50) NOT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `academic_year_levels` (`level_name`) VALUES 
('First Year'), 
('Second Year'), 
('Third Year'), 
('Fourth Year'), 
('Fifth Year');

ALTER TABLE `classrooms` 
  ADD `academic_year_level_id` INT NULL AFTER `academic_year_id`, 
  ADD `course_id` INT NULL AFTER `academic_year_level_id`;

ALTER TABLE `classrooms` 
  ADD CONSTRAINT `classrooms_ay_level_fk` FOREIGN KEY (`academic_year_level_id`) REFERENCES `academic_year_levels`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `classrooms` 
  ADD CONSTRAINT `classrooms_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `timetables` 
  ADD `academic_year_id` INT NULL AFTER `classroom_id`, 
  ADD `academic_year_level_id` INT NULL AFTER `academic_year_id`, 
  ADD `course_id` INT NULL AFTER `academic_year_level_id`, 
  ADD `major_id` INT NULL AFTER `course_id`;

ALTER TABLE `timetables` 
  ADD CONSTRAINT `timetables_ay_fk` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `timetables` 
  ADD CONSTRAINT `timetables_ay_level_fk` FOREIGN KEY (`academic_year_level_id`) REFERENCES `academic_year_levels`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `timetables` 
  ADD CONSTRAINT `timetables_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `timetables` 
  ADD CONSTRAINT `timetables_major_fk` FOREIGN KEY (`major_id`) REFERENCES `majors`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
