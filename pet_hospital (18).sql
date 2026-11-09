-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 04:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pet_hospital`
--
CREATE DATABASE IF NOT EXISTS `pet_hospital` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pet_hospital`;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointments_ID` int(11) NOT NULL,
  `appointment_detail` varchar(200) NOT NULL,
  `pet_ID` int(11) NOT NULL,
  `doctor_ID` int(11) NOT NULL,
  `Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointments_ID`, `appointment_detail`, `pet_ID`, `doctor_ID`, `Date`) VALUES
(1, 'Annual Vaccination', 1, 1, '2025-01-15'),
(2, 'Skin Allergy Check', 2, 4, '2025-03-22'),
(3, 'Diarrhea and Vomiting', 3, 2, '2025-05-10'),
(4, 'Routine Checkup', 4, 5, '2025-08-05'),
(5, 'Dental Cleaning', 5, 3, '2025-11-20'),
(6, 'Heartworm Test', 6, 1, '2025-02-14'),
(7, 'Eye Infection', 7, 7, '2025-04-18'),
(8, 'Ear Mites Treatment', 8, 8, '2025-06-30'),
(9, 'Spay Surgery Consult', 9, 6, '2025-09-12'),
(10, 'Limping Hind Leg', 10, 9, '2025-12-01'),
(11, 'Booster Vaccine', 11, 2, '2025-01-20'),
(12, 'Weight Management', 12, 10, '2025-02-15'),
(13, 'Senior Pet Health Check', 13, 11, '2025-03-05'),
(14, 'Rabies Vaccine', 14, 12, '2025-04-10'),
(15, 'Skin Rash Consult', 15, 13, '2025-05-22'),
(16, 'Vomiting for 2 days', 16, 2, '2025-02-10'),
(17, 'Annual Health Check', 17, 1, '2025-04-05'),
(18, 'Flea and Tick Control', 18, 3, '2025-06-15'),
(19, 'Hair Loss on Back', 19, 4, '2025-07-22'),
(20, 'Spay Consultation', 20, 5, '2025-09-10'),
(21, 'Dental Scaling', 21, 6, '2025-10-30'),
(22, 'Vaccinations', 22, 7, '2025-12-12'),
(23, 'Eye Discharge', 23, 8, '2025-01-18'),
(24, 'Limping Front Leg', 24, 9, '2025-03-05'),
(25, 'Weight Loss Follow-up', 25, 10, '2025-05-20'),
(26, 'Ear Infection', 26, 11, '2025-07-11'),
(27, 'Microchip Implantation', 27, 12, '2025-08-25'),
(28, 'Diarrhea', 28, 13, '2025-10-05'),
(29, 'Skin Allergy', 29, 14, '2025-11-15'),
(30, 'Routine Checkup', 30, 15, '2025-01-08'),
(31, 'Senior Blood Work', 31, 1, '2025-02-20'),
(32, 'Coughing', 32, 2, '2025-03-15'),
(33, 'Rabies Vaccine', 33, 3, '2025-04-10'),
(34, 'Urinary Tract Infection', 34, 4, '2025-05-05'),
(35, 'Neutering Surgery', 35, 5, '2025-06-18'),
(36, 'Bad Breath', 36, 6, '2025-07-22'),
(37, 'Constipation', 37, 7, '2025-08-10'),
(38, 'Heartworm Prevention', 38, 8, '2025-09-05'),
(39, 'Scratching Ears', 39, 9, '2025-10-12'),
(40, 'Eye Checkup', 40, 10, '2025-11-20'),
(41, 'Booster Shot', 41, 11, '2025-03-14'),
(42, 'Post-Surgery Check', 42, 12, '2025-05-28'),
(43, 'Allergy Testing', 43, 13, '2025-08-19'),
(44, 'Tapeworm Treatment', 44, 14, '2025-11-02'),
(45, 'Nail Trimming & Anal Glands', 45, 15, '2025-02-25'),
(46, 'Vomiting Hairballs', 46, 1, '2025-04-30'),
(47, 'Tick Fever Test', 47, 2, '2025-06-15'),
(48, 'Arthritis Consultation', 48, 3, '2025-09-22'),
(49, 'Annual Blood Test', 49, 4, '2025-12-05'),
(50, 'Fungal Skin Infection', 50, 5, '2025-01-25'),
(51, 'Routine Checkup', 1, 3, '2025-01-05'),
(52, 'Loss of Appetite', 2, 5, '2025-01-12'),
(53, 'Rabies Vaccine', 3, 7, '2025-02-18'),
(54, 'Limping Hind Leg', 4, 9, '2025-03-22'),
(55, 'Dental Cleaning', 5, 11, '2025-04-10'),
(56, 'Ear Mites Check', 6, 13, '2025-05-15'),
(57, 'Vomiting', 7, 15, '2025-06-20'),
(58, 'Skin Allergy', 8, 2, '2025-07-05'),
(59, 'Booster Vaccine', 9, 4, '2025-08-14'),
(60, 'Obesity Consult', 10, 6, '2025-09-09'),
(61, 'Eye Discharge', 11, 8, '2025-10-11'),
(62, 'Coughing', 12, 10, '2025-11-25'),
(63, 'Diarrhea', 13, 12, '2025-12-05'),
(64, 'Spay Consultation', 14, 14, '2025-01-10'),
(65, 'Neutering Surgery', 15, 1, '2025-02-14'),
(66, 'Microchip Implantation', 16, 3, '2025-03-02'),
(67, 'Flea and Tick Control', 17, 5, '2025-04-18'),
(68, 'Urinary Tract Infection', 18, 7, '2025-05-22'),
(69, 'Hair Loss', 19, 9, '2025-06-30'),
(70, 'Nail Trimming & Anal Glands', 20, 11, '2025-07-15'),
(71, 'Arthritis Check', 21, 13, '2025-08-08'),
(72, 'Blood Test', 22, 15, '2025-09-12'),
(73, 'Heartworm Prevention', 23, 2, '2025-10-20'),
(74, 'Constipation', 24, 4, '2025-11-05'),
(75, 'Routine Checkup', 25, 6, '2025-12-18'),
(76, 'Bad Breath', 26, 8, '2025-01-15'),
(77, 'Gingivitis', 27, 10, '2025-02-20'),
(78, 'Senior Pet Check', 28, 12, '2025-03-10'),
(79, 'Tapeworm Treatment', 29, 14, '2025-04-05'),
(80, 'Allergy Testing', 30, 1, '2025-05-12'),
(81, 'Fungal Skin Infection', 31, 3, '2025-02-28'),
(82, 'Vomiting Hairballs', 32, 5, '2025-04-02'),
(83, 'Tick Fever Test', 33, 7, '2025-06-10'),
(84, 'Post-Surgery Check', 34, 9, '2025-08-25'),
(85, 'Eye Infection', 35, 11, '2025-10-30'),
(86, 'Routine Checkup', 36, 13, '2025-01-05'),
(87, 'Annual Vaccination', 37, 15, '2025-03-15'),
(88, 'Rabies Vaccine', 38, 2, '2025-05-20'),
(89, 'Limping Front Leg', 39, 4, '2025-07-25'),
(90, 'Dental Scaling', 40, 6, '2025-09-10'),
(91, 'Ear Infection', 41, 8, '2025-11-15'),
(92, 'Diarrhea', 42, 10, '2025-01-20'),
(93, 'Skin Rash', 43, 12, '2025-03-05'),
(94, 'Booster Shot', 44, 14, '2025-05-10'),
(95, 'Weight Management', 45, 1, '2025-07-15'),
(96, 'Conjunctivitis', 46, 3, '2025-09-20'),
(97, 'Asthma Consult', 47, 5, '2025-11-25'),
(98, 'Diarrhea and Vomiting', 48, 7, '2025-01-30'),
(99, 'Spay Surgery Consult', 49, 9, '2025-03-05'),
(100, 'Neutering Surgery', 50, 11, '2025-05-18'),
(101, 'Microchip', 1, 13, '2025-07-22'),
(102, 'Flea and Tick Control', 2, 15, '2025-09-14'),
(103, 'UTI Follow-up', 3, 2, '2025-11-08'),
(104, 'Hair Loss on Back', 4, 4, '2025-01-12'),
(105, 'Anal Glands', 5, 6, '2025-03-20'),
(106, 'Arthritis Consultation', 6, 8, '2025-05-25'),
(107, 'Blood Work', 7, 10, '2025-07-30'),
(108, 'Heartworm Test', 8, 12, '2025-10-05'),
(109, 'Constipation', 9, 14, '2025-12-10'),
(110, 'Routine Checkup', 10, 1, '2025-02-14'),
(111, 'Bad Breath', 11, 3, '2025-04-18'),
(112, 'Tartar Buildup', 12, 5, '2025-06-22'),
(113, 'Senior Health Check', 13, 7, '2025-08-30'),
(114, 'Tapeworm', 14, 9, '2025-10-15'),
(115, 'Allergy Testing', 15, 11, '2025-12-01'),
(116, 'Fungal Infection', 16, 13, '2025-02-10'),
(117, 'Hairballs', 17, 15, '2025-04-25'),
(118, 'Tick Fever Test', 18, 2, '2025-06-30'),
(119, 'Post-Op Check', 19, 4, '2025-09-05'),
(120, 'Eye Ulcer Check', 20, 6, '2025-11-20'),
(121, 'Wellness Exam', 21, 8, '2025-01-25'),
(122, 'Annual Vaccine', 22, 10, '2025-03-10'),
(123, 'Rabies Shot', 23, 12, '2025-05-15'),
(124, 'Limping', 24, 14, '2025-07-20'),
(125, 'Dental Scaling', 25, 1, '2025-09-25'),
(126, 'Ear Yeast Infection', 26, 3, '2025-11-30'),
(127, 'Chronic Diarrhea', 27, 5, '2025-02-05'),
(128, 'Skin Rash', 28, 7, '2025-04-10'),
(129, 'Booster Shots', 29, 9, '2025-06-15'),
(130, 'Weight Loss Follow-up', 30, 11, '2025-08-20'),
(131, 'Eye Infection', 31, 13, '2025-10-25'),
(132, 'Asthma Follow-up', 32, 15, '2025-12-10'),
(133, 'Acute Vomiting', 33, 2, '2025-01-15'),
(134, 'Spay Consultation', 34, 4, '2025-03-20'),
(135, 'Neutering', 35, 6, '2025-05-25'),
(136, 'Microchip', 36, 8, '2025-08-10'),
(137, 'Flea Prevention', 37, 10, '2025-10-15'),
(138, 'UTI Check', 38, 12, '2025-12-20'),
(139, 'Hair Loss', 39, 14, '2025-02-25'),
(140, 'Anal Gland Expression', 40, 1, '2025-04-30'),
(141, 'Arthritis Pain', 41, 3, '2025-07-05'),
(142, 'Blood Panel', 42, 5, '2025-09-10'),
(143, 'Heartworm Meds', 43, 7, '2025-11-15'),
(144, 'Constipation', 44, 9, '2025-01-20'),
(145, 'Routine Checkup', 45, 11, '2025-03-25'),
(146, 'Bad Breath', 46, 13, '2025-05-30'),
(147, 'Gingivitis', 47, 15, '2025-08-05'),
(148, 'Senior Wellness', 48, 2, '2025-10-10'),
(149, 'Tapeworm Meds', 49, 4, '2025-12-15'),
(150, 'Allergy Consult', 50, 6, '2025-12-28'),
(160, 'ทดสอบ', 54, 13, '2026-03-26'),
(161, 'sd', 54, 14, '2026-04-01'),
(162, 'asd', 44, 8, '2026-04-01'),
(163, 'dsafsd', 54, 14, '2026-04-01'),
(164, 'd', 24, 8, '2026-04-01'),
(165, 'asd', 13, 16, '2026-04-01');

-- --------------------------------------------------------

--
-- Table structure for table `breed`
--

CREATE TABLE `breed` (
  `breed_ID` int(11) NOT NULL,
  `breed_detail` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `breed`
--

INSERT INTO `breed` (`breed_ID`, `breed_detail`) VALUES
(1, 'Golden Retriever'),
(2, 'Labrador Retriever'),
(3, 'Siberian Husky'),
(4, 'Beagle'),
(5, 'Chihuahua'),
(6, 'Pug'),
(7, 'Shih Tzu'),
(8, 'Poodle'),
(9, 'Siamese (แมววิเชียรมาศ)'),
(10, 'Persian'),
(11, 'Scottish Fold'),
(12, 'Maine Coon'),
(13, 'Bengal'),
(14, 'Sphynx'),
(15, 'Khao Manee (แมวขาวมณี)');

-- --------------------------------------------------------

--
-- Table structure for table `diagnoses`
--

CREATE TABLE `diagnoses` (
  `diagnoses_ID` int(50) NOT NULL,
  `appointment_ID` int(50) NOT NULL,
  `diagnosisDescription` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnoses`
--

INSERT INTO `diagnoses` (`diagnoses_ID`, `appointment_ID`, `diagnosisDescription`) VALUES
(1, 1, 'Physical exam normal. Administered core vaccines (DHPP, Rabies). No adverse reactions.'),
(2, 2, 'Atopic dermatitis suspected. Found redness between toes. Prescribed Apoquel and medicated shampoo.'),
(3, 3, 'Gastroenteritis. Likely dietary indiscretion. Prescribed anti-nausea meds and bland diet (ID).'),
(4, 4, 'Overall health is good. Weight stable. Heart and lungs sound clear.'),
(5, 5, 'Grade 2 periodontal disease. Performed ultrasonic scaling and polishing. No extractions needed.'),
(6, 6, 'Heartworm 4DX test: Negative. Continued monthly preventative treatment.'),
(7, 7, 'Conjunctivitis in left eye. Minor corneal scratch. Prescribed antibiotic eye drops.'),
(8, 8, 'Confirmed Otodectes cynotis (ear mites). Cleaned ears and applied topical treatment.'),
(9, 9, 'Pre-surgery blood work normal. Scheduled for spaying next Tuesday.'),
(10, 10, 'Mild luxating patella on right hind leg. Recommended joint supplements and rest.'),
(11, 11, 'Booster shots administered as scheduled. Patient in good health.'),
(12, 12, 'Patient is 15% overweight. Discussed calorie reduction and increased activity.'),
(13, 13, 'Early signs of kidney insufficiency found in blood work. Switched to KD diet.'),
(14, 14, 'Annual rabies vaccination given. Next due date: 1 year.'),
(15, 15, 'Fungal infection confirmed (Ringworm). Prescribed antifungal cream and oral meds.'),
(16, 16, 'Mild gastritis. Possible hairball irritation. Prescribed laxatone.'),
(17, 17, 'Complete blood count (CBC) within normal limits. General health excellent.'),
(18, 18, 'Heavy flea infestation. Administered Bravecto. Advised environment cleaning.'),
(19, 19, 'Flea allergy dermatitis. Hair loss on tail base. Prescribed steroids for itching.'),
(20, 20, 'Consulted owner about spay procedure. Owner decided to wait for next cycle.'),
(21, 21, 'Heavy tartar buildup. Scaling performed under general anesthesia.'),
(22, 22, 'Multi-cat household vaccine protocol updated. All vitals normal.'),
(23, 23, 'Bacterial eye infection. Prescribed Terramycin ointment for 7 days.'),
(24, 24, 'Muscle strain in front limb. No fracture found on X-ray. Prescribed NSAIDs.'),
(25, 25, 'Weight gain of 0.5kg achieved. Nutrition plan working well.'),
(26, 26, 'Otitis Externa (Outer ear infection). Yeast found in ear swab. Cleaned and treated.'),
(27, 27, 'Microchip implanted successfully at the shoulder blades. Registered in system.'),
(28, 28, 'Colitis. Found traces of hookworms in stool. Prescribed dewormer (Drontal).'),
(29, 29, 'Food allergy suspected. Recommended 8-week elimination diet (Hydrolyzed protein).'),
(30, 30, 'Routine physical exam: No abnormalities found. Heart rate 110 bpm.'),
(31, 31, 'Senior panel shows elevated liver enzymes. Recommended follow-up ultrasound.'),
(32, 32, 'Kennel cough (Infectious Tracheobronchitis). Prescribed cough suppressants.'),
(33, 33, 'Administered 3-year rabies vaccine protocol.'),
(34, 34, 'Cystitis confirmed via urinalysis. Crystals found. Prescribed antibiotics.'),
(35, 35, 'Neutering completed successfully. Recovering well in ward.'),
(36, 36, 'Halitosis caused by tooth decay (Tooth 104). Recommended extraction.'),
(37, 37, 'Mild constipation. Dehydration suspected. Administered SQ fluids.'),
(38, 38, 'Heartworm prevention injection (ProHeart) administered.'),
(39, 39, 'Ear infection due to excessive wax. Cleaned and prescribed ear drops.'),
(40, 40, 'Early cataract formation in both eyes. No treatment needed at this stage.'),
(41, 41, 'Booster vaccine given. Check-up for general vitals: Normal.'),
(42, 42, 'Incision site healing perfectly. Sutures removed today.'),
(43, 43, 'Skin scrapings negative for mites. Allergy panel sent to lab.'),
(44, 44, 'Dipylidium caninum segments found. Prescribed Praziquantel.'),
(45, 45, 'Nails trimmed and anal glands expressed (full, thick brown fluid).'),
(46, 46, 'Hairball accumulation in stomach. Prescribed specialized high-fiber diet.'),
(47, 47, 'Ehrlichia positive (Tick fever). Started 28-day Doxycycline course.'),
(48, 48, 'Osteoarthritis in hips. Prescribed Gabapentin for pain management.'),
(49, 49, 'Routine blood test for senior pet. Creatinine slightly high.'),
(50, 50, 'Malassezia pachydermatis (Yeast) on skin. Prescribed Malaseb baths.'),
(51, 51, 'Checkup: All systems normal. Teeth are exceptionally clean.'),
(52, 52, 'Decreased appetite due to dental pain. Scheduled for cleaning.'),
(53, 53, 'Rabies vaccine administered without issue.'),
(54, 54, 'Confirmed ACL tear in left hind leg. Recommended orthopedic surgery.'),
(55, 55, 'Dental scaling and polishing completed. Minor gingival recession.'),
(56, 56, 'Confirmed ear mites via microscope. Treated with Revolution.'),
(57, 57, 'Acute vomiting episode. Administered anti-emetic injection (Cerenia).'),
(58, 58, 'Contact dermatitis. Likely from new carpet cleaner. Prescribed cream.'),
(59, 59, 'DHLPP booster administered. Patient healthy.'),
(60, 60, 'Body Condition Score (BCS) is 8/9. Prescribed weight loss diet.'),
(61, 61, 'Nasolacrimal duct obstruction. Flushed ducts under sedation.'),
(62, 62, 'Bronchitis. Prescribed nebulization and antibiotics.'),
(63, 63, 'Loose stools for 3 days. Prescribed probiotics and Kaolin-Pectin.'),
(64, 64, 'Discussed spaying benefits/risks. Scheduled for next month.'),
(65, 65, 'Castration surgery successful. No complications.'),
(66, 66, 'Microchip placed and verified with scanner.'),
(67, 67, 'Flea/Tick prevention (Frontline) applied.'),
(68, 68, 'Bacterial UTI. Prescribed Clavamox for 10 days.'),
(69, 69, 'Alopecia caused by endocrine disorder. Recommended thyroid test.'),
(70, 70, 'Glands expressed. Nails cut short. No infection noted.'),
(71, 71, 'Stiff joints in the morning. Prescribed Glucosamine/Chondroitin.'),
(72, 72, 'Blood panel normal. Cholesterol slightly elevated.'),
(73, 73, 'Negative for heartworm. Provided 6 months of Heartgard.'),
(74, 74, 'Prescribed Lactulose for chronic constipation.'),
(75, 75, 'Annual physical: Heart, lungs, and abdomen all normal.'),
(76, 76, 'Heavy plaque on back molars. Home brushing recommended.'),
(77, 77, 'Redness in gums. Prescribed chlorhexidine gel.'),
(78, 78, 'Senior pet showing cognitive decline. Discussed enrichment.'),
(79, 79, 'Found tapeworm segments. Deworming treatment given.'),
(80, 80, 'Intradermal skin test performed. Allergic to house dust mites.'),
(81, 81, 'Ringworm treatment continued. Skin lesions healing.'),
(82, 82, 'Frequent hairballs. Switched to hairball control dry food.'),
(83, 83, 'Negative for tick fever. Blood cells count is normal.'),
(84, 84, 'Surgical site clean. No signs of infection or swelling.'),
(85, 85, 'Blepharitis (Eyelid inflammation). Prescribed warm compress and drops.'),
(86, 86, 'Healthy puppy exam. Weight 5kg. Heart clear.'),
(87, 87, 'Core vaccinations (FVRCP/Rabies) administered.'),
(88, 88, 'Rabies vaccine given. Vaccination tag issued.'),
(89, 89, 'Bruised paw pad. Prescribed antiseptic soak.'),
(90, 90, 'Extensive dental work. 2 incisors extracted.'),
(91, 91, 'Deep ear infection. Swab shows Pseudomonas. Prescribed strong meds.'),
(92, 92, 'Diarrhea due to change in brand of treats. Stop treats for 1 week.'),
(93, 93, 'Hives (Urticaria). Administered Benadryl injection.'),
(94, 94, 'Feline leukemia vaccine booster given.'),
(95, 95, 'Weight stable at target goal. Continue current maintenance diet.'),
(96, 96, 'Pink eye. Allergic reaction to pollen. Prescribed drops.'),
(97, 97, 'Feline asthma. Prescribed inhaler (Fluticasone).'),
(98, 98, 'Severe dehydration from vomiting. Hospitalized for IV fluids.'),
(99, 99, 'Pre-surgical exam: Grade 1 heart murmur noted. Proceed with caution.'),
(100, 100, 'Scrotal ablation performed with neutering. Healing well.'),
(101, 101, 'Microchip serial number 985112000XXXXXX recorded.'),
(102, 102, 'Applied NexGard for flea and tick control.'),
(103, 103, 'Follow-up urinalysis: Clear. No more bacteria or crystals.'),
(104, 104, 'Psychogenic alopecia (stress-related over-grooming). Advised Feliway.'),
(105, 105, 'Anal glands were impacted. Flushed with saline.'),
(106, 106, 'Bilateral hip dysplasia confirmed by X-ray. Prescribed pain relief.'),
(107, 107, 'Comprehensive blood panel: All values normal for a 7-year-old dog.'),
(108, 108, 'Antigen test negative. No heartworm detected.'),
(109, 109, 'Prescribed high-fiber diet and increased water intake.'),
(110, 110, 'Patient healthy. Coat is shiny, eyes are bright.'),
(111, 111, 'Dental consult: Scaling highly recommended due to bad breath.'),
(112, 112, 'Tartar removed from premolars. Gums look healthier.'),
(113, 113, 'Senior wellness: Blood pressure 140/90. Monitor for hypertension.'),
(114, 114, 'Prescribed deworming tablet for tapeworm.'),
(115, 115, 'Allergy to chicken confirmed. Prescribed lamb and rice diet.'),
(116, 116, 'Systemic fungal infection. Started long-term Itraconazole.'),
(117, 117, 'Vomiting hairballs every week. Prescribed cat grass and malt paste.'),
(118, 118, 'Borderline anemia due to past tick fever. Prescribed iron supplements.'),
(119, 119, 'Post-op day 14. Skin completely healed. Activities can resume.'),
(120, 120, 'Small corneal ulcer. Prescribed eye gel (EDTA). Do not use steroids.'),
(121, 121, 'Physical exam: Normal. No lumps or bumps found.'),
(122, 122, 'Annual core vaccines given (Parvo, Distemper, Adenovirus).'),
(123, 123, 'Administered annual rabies shot.'),
(124, 124, 'Interdigital cyst between toes. Prescribed antibiotics.'),
(125, 125, 'Professional dental cleaning performed today.'),
(126, 126, 'Yeast otitis. Advised ear drying after baths.'),
(127, 127, 'Inflammatory Bowel Disease (IBD) suspected. Prescribed Prednisolone.'),
(128, 128, 'Heat rash. Prescribed soothing aloe-based spray.'),
(129, 129, 'Boosters for Lepto and Parvo administered.'),
(130, 130, 'Weight down by 200g. Owner following feeding guide strictly.'),
(131, 131, 'Uveitis (Inflammation inside eye). Referred to specialist.'),
(132, 132, 'Asthma under control. Breathing rate 20 breaths per minute.'),
(133, 133, 'Pancreatitis ruled out. Simple upset stomach. Prescribed Pepcid.'),
(134, 134, 'Owner ready for spaying. Scheduled for Friday morning.'),
(135, 135, 'Neutering: Patient had retained testicle (Cryptorchid). Surgery successful.'),
(136, 136, 'New microchip registered under owner\'s phone number.'),
(137, 137, 'Flea dirt found. Prescribed oral flea medication (Comfortis).'),
(138, 138, 'Suspected UTI. Culture and sensitivity test sent to lab.'),
(139, 139, 'Mange mites (Demodex) found. Prescribed Ivermectin treatment.'),
(140, 140, 'Routine gland expression. Nails trimmed very short as requested.'),
(141, 141, 'Arthritis flare-up due to cold weather. Increased dosage of Rimadyl.'),
(142, 142, 'Pre-anesthetic blood panel: Liver and Kidney functions perfect.'),
(143, 143, 'Purchased 12-month supply of heartworm prevention.'),
(144, 144, 'Enema performed to relieve severe fecal impaction.'),
(145, 145, 'Wellness visit: BCS 5/9 (Ideal). Keep up the good work.'),
(146, 146, 'Periodontal disease Grade 3. Multiple extractions needed soon.'),
(147, 147, 'Gingivitis improved after switching to T/D dental food.'),
(148, 148, 'Senior pet: No heart murmurs. Eyes show minor lenticular sclerosis.'),
(149, 149, 'Routine deworming for outdoor cat.'),
(150, 150, 'Environmental allergy. Advised wiping paws after walks.'),
(162, 160, 'ทดสอบ'),
(163, 161, 'f'),
(164, 162, 'f'),
(165, 163, 'ๅ'),
(166, 164, 'q');

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `doctor_ID` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `phoneNumber` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`doctor_ID`, `name`, `phoneNumber`) VALUES
(1, 'Dr. Somchai Rakdee', 811112222),
(2, 'Dr. Nattapong Srisuk', 822223333),
(3, 'Dr. Pimchanok Wongsuwan', 833334444),
(4, 'Dr. John Smith', 844445555),
(5, 'Dr. Sarah Connor', 855556666),
(6, 'Dr. Supot Jaidee', 866667777),
(7, 'Dr. Emily Chen', 877778888),
(8, 'Dr. David Miller', 888889999),
(9, 'Dr. Kanya Phatthana', 899990000),
(10, 'Dr. Robert Brown', 900001111),
(11, 'Dr. Araya Chaimongkol', 911112222),
(12, 'Dr. Michael Scott', 922223333),
(13, 'Dr. Lisa Wong', 933334444),
(14, 'Dr. Chatchai Mungmee', 944445555),
(15, 'Dr. William Turner', 955556666),
(16, 'Natchapon Taolae', 865134734),
(18, 'somsom', 0),
(19, 'AudNatchapon', 0);

-- --------------------------------------------------------

--
-- Table structure for table `medication`
--

CREATE TABLE `medication` (
  `medicine_ID` int(4) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` int(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medication`
--

INSERT INTO `medication` (`medicine_ID`, `name`, `price`) VALUES
(1, 'Amoxicillin (ยาปฏิชีวนะ)', 150),
(2, 'Clavaseptin (ยาปฏิชีวนะ)', 280),
(3, 'Doxycycline (ยาปฏิชีวนะ)', 180),
(4, 'Meloxicam (ยาลดอักเสบ/แก้ปวด)', 220),
(5, 'Carprofen (ยาลดอักเสบ/แก้ปวด)', 350),
(6, 'Tramadol (ยาแก้ปวดรุนแรง)', 120),
(7, 'Gabapentin (ยาแก้ปวดเส้นประสาท)', 150),
(8, 'Bravecto (ยากำจัดเห็บหมัด)', 950),
(9, 'NexGard (ยากำจัดเห็บหมัด)', 450),
(10, 'Prednisolone (ยาสเตียรอยด์)', 80),
(11, 'Chlorpheniramine (ยาแก้แพ้)', 50),
(12, 'Maropitant / Cerenia (ยาลดอาเจียน)', 480),
(13, 'Metronidazole (ยาแก้ท้องเสีย)', 140),
(14, 'Furosemide (ยาขับปัสสาวะ)', 100),
(15, 'Enalapril (ยาลดความดัน/โรคหัวใจ)', 180);

-- --------------------------------------------------------

--
-- Table structure for table `owner`
--

CREATE TABLE `owner` (
  `owner_ID` int(9) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `phonenumber` varchar(10) DEFAULT NULL,
  `email` varchar(20) DEFAULT NULL,
  `address` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner`
--

INSERT INTO `owner` (`owner_ID`, `name`, `phonenumber`, `email`, `address`) VALUES
(1, 'Somsak Sae-tang', '0812345670', 'somsak@gmail.com', '12/3 Sukhumvit, BKK'),
(2, 'Mary Johnson', '0823456781', 'mary.j@yahoo.com', '45 Patong, Phuket'),
(3, 'Wichai Thongkam', '0834567892', 'wichai.t@hotmail.com', '78/9 Nimman, Chiang Mai'),
(4, 'Peter Parker', '0845678903', 'peter.p@gmail.com', '11 Silom, BKK'),
(5, 'Kamonwan Suksri', '0856789014', 'kamonwan@gmail.com', '88/8 Ladprao, BKK'),
(6, 'Satoshi Nakamoto', '0867890125', 'satoshi@crypto.com', '99 Thonglor, BKK'),
(7, 'Nadech Kugimiya', '0878901236', 'nadech@ch3.com', '55 Rama 9, BKK'),
(8, 'Emma Watson', '0889012347', 'emma.w@gmail.com', '22 Hua Hin, Prachuap'),
(9, 'Somying Rakchat', '0890123458', 'somying@yahoo.com', '44/4 Pattaya, Chonburi'),
(10, 'Kenji Suzuki', '0901234569', 'kenji@hotmail.com', '77 Ekkamai, BKK'),
(11, 'Pornpan Suksawat', '0912345670', 'pornpan@gmail.com', '12 Sathorn, BKK'),
(12, 'Oliver Twist', '0923456781', 'oliver@gmail.com', '33 Asoke, BKK'),
(13, 'Siriporn Chokdee', '0934567892', 'siriporn@yahoo.com', '56/7 Rangsit, Pathum Thani'),
(14, 'Liam Neeson', '0945678903', 'liam@action.com', '89/1 Samui, Surat Thani'),
(15, 'Anusorn Petch', '0956789014', 'anusorn@gmail.com', '101/2 Bangna, BKK'),
(16, 'Sophia Anderson', '0967890125', 'sophia.a@gmail.com', '42 Chiang Mai'),
(17, 'Thawatchai M.', '0978901236', 'thawatchai@mail.com', '112/5 Nonthaburi'),
(18, 'Carlos Santana', '0989012347', 'carlos@music.com', '88 Kata, Phuket'),
(19, 'Jiraporn S.', '0990123458', 'jiraporn@gmail.com', '55/9 Rayong'),
(20, 'Alice Wonderland', '0601234569', 'alice@gmail.com', '7/7 BKK'),
(21, 'Wipawee Somsri', '0811122334', 'wipawee@gmail.com', '12/4 Rama 4, BKK'),
(22, 'James Bond', '0822233445', 'james.b@mi6.com', '007 Sukhumvit 71, BKK'),
(23, 'Suriya Rungroj', '0833344556', 'suriya.r@hotmail.com', '88/12 Chiang Rai'),
(24, 'Natasha Romanoff', '0844455667', 'natasha@avengers.com', '99 Pattaya, Chonburi'),
(25, 'Panya Wong', '0855566778', 'panya.w@gmail.com', '54 Khon Kaen'),
(26, 'Bruce Wayne', '0866677889', 'bruce@wayne.com', '101 Silom, BKK'),
(27, 'Malee Rakmaew', '0877788990', 'malee.cat@yahoo.com', '22/3 Ladprao, BKK'),
(28, 'Clark Kent', '0888899001', 'clark.k@daily.com', '45 Asoke, BKK'),
(29, 'Kittipong T.', '0899900112', 'kittipong@gmail.com', '78 Udon Thani'),
(30, 'Tony Stark', '0900011223', 'tony@stark.com', '10 Malibu, Phuket'),
(31, 'Jaruwan Jai-dee', '0911122334', 'jaruwan@hotmail.com', '11/11 Bang Kapi, BKK'),
(32, 'Steve Rogers', '0922233445', 'steve.r@gmail.com', '4 Brooklyn, Chiang Mai'),
(33, 'Ananya P.', '0933344556', 'ananya@yahoo.com', '55/5 Songkhla'),
(34, 'Diana Prince', '0944455667', 'diana@amazon.com', '99/9 Hua Hin'),
(35, 'Nipon Suksabai', '0955566778', 'nipon@gmail.com', '123/45 Nakhon Ratchasima'),
(36, 'Arthur Curry', '0966677889', 'arthur@atlantis.com', '7 Koh Samui'),
(37, 'Orn-uma K.', '0977788990', 'ornuma@gmail.com', '88/8 Nonthaburi'),
(38, 'Barry Allen', '0988899001', 'barry@star.com', '99 Pathum Thani'),
(39, 'Sompong N.', '0999900112', 'sompong@hotmail.com', '44/4 Samut Prakan'),
(40, 'Victor Stone', '0800011223', 'victor@labs.com', '55/1 Rayong'),
(41, 'Wandee C.', '0812345671', 'wandee@gmail.com', '12 BKK'),
(42, 'Thor Odinson', '0823456782', 'thor@asgard.com', '1 Valhalla, BKK'),
(43, 'Kriangsak M.', '0834567893', 'kriangsak@yahoo.com', '34/5 Ayutthaya'),
(44, 'Stephen Strange', '0845678904', 'stephen@sanctum.com', '177A Bleecker, BKK'),
(45, 'Rattana J.', '0856789015', 'rattana@gmail.com', '56 Chonburi'),
(46, 'Peter Quill', '0867890126', 'peter.q@galaxy.com', '88/9 Pattaya'),
(47, 'Chatchawan P.', '0878901237', 'chatchawan@hotmail.c', '90/1 Chiang Mai'),
(48, 'Wanda Maximoff', '0889012348', 'wanda@westview.com', '22/2 Phuket'),
(49, 'Pranee S.', '0890123459', 'pranee@gmail.com', '11/4 Khon Kaen'),
(50, 'Scott Lang', '0901234560', 'scott@pym.com', '55 BKK'),
(57, 'Natchapon Taolae', '0865134734', 'peeaut2548@gmail.com', '28/592 nakorn pathom Thailand'),
(60, 'Natchapon Taolae', '0865134734', 'peeaut2548@gmail.com', '');

-- --------------------------------------------------------

--
-- Table structure for table `pet`
--

CREATE TABLE `pet` (
  `pet_ID` int(9) NOT NULL,
  `owner_ID` int(9) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `breed_ID` int(10) DEFAULT NULL,
  `age` int(100) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet`
--

INSERT INTO `pet` (`pet_ID`, `owner_ID`, `name`, `dob`, `breed_ID`, `age`, `gender`) VALUES
(1, 1, 'Milo', NULL, 1, 3, 'Male'),
(2, 2, 'Luna', NULL, 10, 2, 'Female'),
(3, 3, 'Oliang', NULL, 6, 5, 'Male'),
(4, 4, 'Bella', NULL, 8, 1, 'Female'),
(5, 5, 'Chao-Kuay', NULL, 9, 4, 'Male'),
(6, 6, 'Hachi', NULL, 3, 2, 'Male'),
(7, 7, 'Muffin', NULL, 11, 3, 'Female'),
(8, 8, 'Cooper', NULL, 2, 6, 'Male'),
(9, 9, 'Kati', NULL, 15, 1, 'Female'),
(10, 10, 'Taro', NULL, 5, 7, 'Male'),
(11, 11, 'Daisy', NULL, 4, 4, 'Female'),
(12, 12, 'Oliver', NULL, 12, 2, 'Male'),
(13, 13, 'Thong-Daeng', NULL, 7, 8, 'Female'),
(14, 14, 'Max', NULL, 13, 3, 'Male'),
(15, 15, 'Leo', NULL, 14, 5, 'Male'),
(16, 16, 'Shadow', NULL, 3, 4, 'Male'),
(17, 17, 'Namo', NULL, 1, 2, 'Male'),
(18, 18, 'Carlos Jr.', NULL, 5, 6, 'Male'),
(19, 19, 'Mimi', NULL, 10, 3, 'Female'),
(20, 20, 'Cheshire', NULL, 11, 1, 'Male'),
(21, 21, 'Boonrod', NULL, 6, 8, 'Male'),
(22, 22, 'Vesper', NULL, 12, 2, 'Female'),
(23, 23, 'Lamyai', NULL, 9, 5, 'Female'),
(24, 24, 'Clint', NULL, 2, 4, 'Male'),
(25, 25, 'Jao-Tui', NULL, 4, 3, 'Male'),
(26, 26, 'Robin', NULL, 13, 2, 'Male'),
(27, 27, 'Khao-Tom', NULL, 15, 1, 'Male'),
(28, 28, 'Krypto', NULL, 1, 5, 'Male'),
(29, 29, 'Mamuang', NULL, 8, 4, 'Female'),
(30, 30, 'Jarvis', NULL, 14, 2, 'Male'),
(31, 31, 'Nom-Sod', NULL, 7, 6, 'Female'),
(32, 32, 'Bucky', NULL, 3, 3, 'Male'),
(33, 33, 'Cha-Yen', NULL, 9, 2, 'Female'),
(34, 34, 'Trevor', NULL, 2, 4, 'Male'),
(35, 35, 'O-Ro', NULL, 6, 7, 'Male'),
(36, 36, 'Mera', NULL, 12, 3, 'Female'),
(37, 37, 'Thong-Muan', NULL, 5, 2, 'Female'),
(38, 38, 'Flash', NULL, 4, 1, 'Male'),
(39, 39, 'Kapook', NULL, 8, 5, 'Female'),
(40, 40, 'Cyborg', NULL, 13, 4, 'Male'),
(41, 41, 'Waan-Jai', NULL, 10, 3, 'Female'),
(42, 42, 'Loki', NULL, 14, 2, 'Male'),
(43, 43, 'Chok-Dee', NULL, 1, 6, 'Male'),
(44, 44, 'Wong', NULL, 11, 4, 'Male'),
(45, 45, 'Ruay', NULL, 7, 5, 'Female'),
(46, 46, 'Groot', NULL, 2, 1, 'Male'),
(47, 47, 'Heng', NULL, 9, 3, 'Male'),
(48, 48, 'Vision', NULL, 15, 2, 'Male'),
(49, 49, 'Kati', NULL, 6, 4, 'Female'),
(50, 50, 'Hope', NULL, 5, 3, 'Female'),
(54, 57, 'Mee Poo', NULL, 3, 7, 'Male'),
(57, 57, 'somsom', '2022-01-05', 5, 4, 'Male');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `receipt_ID` int(11) NOT NULL,
  `appointment_ID` int(11) NOT NULL,
  `total_medication_price` decimal(10,2) DEFAULT 0.00,
  `service_fee` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `payment_method` enum('Cash','Transfer','Credit Card') DEFAULT 'Cash',
  `payment_status` enum('Pending','Paid','Cancelled') DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`receipt_ID`, `appointment_ID`, `total_medication_price`, `service_fee`, `grand_total`, `payment_method`, `payment_status`, `payment_date`) VALUES
(1, 1, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(2, 2, 50.00, 200.00, 250.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(3, 3, 140.00, 200.00, 340.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(4, 4, 450.00, 200.00, 650.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(5, 5, 280.00, 200.00, 480.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(6, 6, 180.00, 200.00, 380.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(7, 7, 180.00, 200.00, 380.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(8, 8, 220.00, 200.00, 420.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(9, 9, 350.00, 200.00, 550.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(10, 10, 120.00, 200.00, 320.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(11, 11, 0.00, 200.00, 200.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(12, 12, 0.00, 200.00, 200.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(13, 13, 0.00, 200.00, 200.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(14, 14, 0.00, 200.00, 200.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(15, 15, 0.00, 200.00, 200.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(16, 16, 480.00, 200.00, 680.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(17, 17, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(18, 18, 450.00, 200.00, 650.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(19, 19, 150.00, 200.00, 350.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(20, 20, 80.00, 200.00, 280.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(21, 21, 280.00, 200.00, 480.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(22, 22, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(23, 23, 180.00, 200.00, 380.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(24, 24, 350.00, 200.00, 550.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(25, 25, 100.00, 200.00, 300.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(26, 26, 220.00, 200.00, 420.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(27, 27, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(28, 28, 140.00, 200.00, 340.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(29, 29, 50.00, 200.00, 250.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(30, 30, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(31, 31, 180.00, 200.00, 380.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(32, 32, 180.00, 200.00, 380.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(33, 33, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(34, 34, 280.00, 200.00, 480.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(35, 35, 120.00, 200.00, 320.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(36, 36, 280.00, 200.00, 480.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(37, 37, 140.00, 200.00, 340.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(38, 38, 450.00, 200.00, 650.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(39, 39, 220.00, 200.00, 420.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(40, 40, 180.00, 200.00, 380.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(41, 41, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(42, 42, 350.00, 200.00, 550.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(43, 43, 80.00, 200.00, 280.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(44, 44, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(45, 45, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(46, 46, 480.00, 200.00, 680.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(47, 47, 180.00, 200.00, 380.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(48, 48, 150.00, 200.00, 350.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(49, 49, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(50, 50, 150.00, 200.00, 350.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(51, 51, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(52, 52, 480.00, 200.00, 680.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(53, 53, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(54, 54, 350.00, 200.00, 550.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(55, 55, 280.00, 200.00, 480.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(56, 56, 450.00, 200.00, 650.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(57, 57, 480.00, 200.00, 680.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(58, 58, 50.00, 200.00, 250.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(59, 59, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(60, 60, 100.00, 200.00, 300.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(61, 61, 180.00, 200.00, 380.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(62, 62, 50.00, 200.00, 250.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(63, 63, 140.00, 200.00, 340.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(64, 64, 80.00, 200.00, 280.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(65, 65, 120.00, 200.00, 320.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(66, 66, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(67, 67, 450.00, 200.00, 650.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(68, 68, 150.00, 200.00, 350.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(69, 69, 80.00, 200.00, 280.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(70, 70, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(71, 71, 150.00, 200.00, 350.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(72, 72, 180.00, 200.00, 380.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(73, 73, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(74, 74, 140.00, 200.00, 340.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(75, 75, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(76, 76, 280.00, 200.00, 480.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(77, 77, 280.00, 200.00, 480.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(78, 78, 180.00, 200.00, 380.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(79, 79, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(80, 80, 80.00, 200.00, 280.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(81, 81, 150.00, 200.00, 350.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(82, 82, 480.00, 200.00, 680.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(83, 83, 180.00, 200.00, 380.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(84, 84, 350.00, 200.00, 550.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(85, 85, 180.00, 200.00, 380.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(86, 86, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(87, 87, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(88, 88, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(89, 89, 350.00, 200.00, 550.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(90, 90, 280.00, 200.00, 480.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(91, 91, 220.00, 200.00, 420.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(92, 92, 140.00, 200.00, 340.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(93, 93, 50.00, 200.00, 250.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(94, 94, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(95, 95, 100.00, 200.00, 300.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(96, 96, 180.00, 200.00, 380.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(97, 97, 80.00, 200.00, 280.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(98, 98, 480.00, 200.00, 680.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(99, 99, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(100, 100, 120.00, 200.00, 320.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(101, 101, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(102, 102, 450.00, 200.00, 650.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(103, 103, 150.00, 200.00, 350.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(104, 104, 50.00, 200.00, 250.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(105, 105, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(106, 106, 150.00, 200.00, 350.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(107, 107, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(108, 108, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(109, 109, 140.00, 200.00, 340.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(110, 110, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(111, 111, 280.00, 200.00, 480.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(112, 112, 280.00, 200.00, 480.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(113, 113, 180.00, 200.00, 380.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(114, 114, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(115, 115, 80.00, 200.00, 280.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(116, 116, 150.00, 200.00, 350.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(117, 117, 480.00, 200.00, 680.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(118, 118, 180.00, 200.00, 380.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(119, 119, 350.00, 200.00, 550.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(120, 120, 180.00, 200.00, 380.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(121, 121, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(122, 122, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(123, 123, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(124, 124, 220.00, 200.00, 420.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(125, 125, 280.00, 200.00, 480.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(126, 126, 220.00, 200.00, 420.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(127, 127, 140.00, 200.00, 340.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(128, 128, 50.00, 200.00, 250.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(129, 129, 950.00, 200.00, 1150.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(130, 130, 100.00, 200.00, 300.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(131, 131, 180.00, 200.00, 380.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(132, 132, 80.00, 200.00, 280.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(133, 133, 480.00, 200.00, 680.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(134, 134, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(135, 135, 120.00, 200.00, 320.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(136, 136, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(137, 137, 450.00, 200.00, 650.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(138, 138, 150.00, 200.00, 350.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(139, 139, 50.00, 200.00, 250.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(140, 140, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(141, 141, 150.00, 200.00, 350.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(142, 142, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(143, 143, 950.00, 200.00, 1150.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(144, 144, 140.00, 200.00, 340.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(145, 145, 950.00, 200.00, 1150.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(146, 146, 430.00, 200.00, 630.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(147, 147, 630.00, 200.00, 830.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(148, 148, 280.00, 200.00, 480.00, 'Cash', 'Paid', '2026-03-17 14:51:44'),
(149, 149, 1090.00, 200.00, 1290.00, 'Credit Card', 'Paid', '2026-03-17 14:51:44'),
(150, 150, 130.00, 200.00, 330.00, 'Transfer', 'Paid', '2026-03-17 14:51:44'),
(151, 4, 450.00, 100.00, 550.00, 'Transfer', 'Paid', '2026-03-17 15:01:24'),
(161, 160, 670.00, 100.00, 770.00, 'Cash', 'Paid', '2026-03-26 06:56:49'),
(162, 162, 0.00, 100.00, 100.00, 'Cash', 'Paid', '2026-04-01 14:20:11'),
(163, 161, 0.00, 100.00, 100.00, 'Cash', 'Paid', '2026-04-01 14:20:13');

-- --------------------------------------------------------

--
-- Table structure for table `receipt_details`
--

CREATE TABLE `receipt_details` (
  `detail_ID` int(11) NOT NULL,
  `receipt_ID` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `receipt_details`
--

INSERT INTO `receipt_details` (`detail_ID`, `receipt_ID`, `item_name`, `item_price`) VALUES
(1, 1, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(2, 2, 'Chlorpheniramine (ยาแก้แพ้)', 50.00),
(3, 3, 'Metronidazole (ยาแก้ท้องเสีย)', 140.00),
(4, 4, 'NexGard (ยากำจัดเห็บหมัด)', 450.00),
(5, 5, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(6, 6, 'Enalapril (ยาลดความดัน/โรคหัวใจ)', 180.00),
(7, 7, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(8, 8, 'Meloxicam (ยาลดอักเสบ/แก้ปวด)', 220.00),
(9, 9, 'Carprofen (ยาลดอักเสบ/แก้ปวด)', 350.00),
(10, 10, 'Tramadol (ยาแก้ปวดรุนแรง)', 120.00),
(11, 16, 'Maropitant / Cerenia (ยาลดอาเจียน)', 480.00),
(12, 17, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(13, 18, 'NexGard (ยากำจัดเห็บหมัด)', 450.00),
(14, 19, 'Amoxicillin (ยาปฏิชีวนะ)', 150.00),
(15, 20, 'Prednisolone (ยาสเตียรอยด์)', 80.00),
(16, 21, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(17, 22, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(18, 23, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(19, 24, 'Carprofen (ยาลดอักเสบ/แก้ปวด)', 350.00),
(20, 25, 'Furosemide (ยาขับปัสสาวะ)', 100.00),
(21, 26, 'Meloxicam (ยาลดอักเสบ/แก้ปวด)', 220.00),
(22, 27, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(23, 28, 'Metronidazole (ยาแก้ท้องเสีย)', 140.00),
(24, 29, 'Chlorpheniramine (ยาแก้แพ้)', 50.00),
(25, 30, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(26, 31, 'Enalapril (ยาลดความดัน/โรคหัวใจ)', 180.00),
(27, 32, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(28, 33, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(29, 34, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(30, 35, 'Tramadol (ยาแก้ปวดรุนแรง)', 120.00),
(31, 36, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(32, 37, 'Metronidazole (ยาแก้ท้องเสีย)', 140.00),
(33, 38, 'NexGard (ยากำจัดเห็บหมัด)', 450.00),
(34, 39, 'Meloxicam (ยาลดอักเสบ/แก้ปวด)', 220.00),
(35, 40, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(36, 41, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(37, 42, 'Carprofen (ยาลดอักเสบ/แก้ปวด)', 350.00),
(38, 43, 'Prednisolone (ยาสเตียรอยด์)', 80.00),
(39, 44, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(40, 45, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(41, 46, 'Maropitant / Cerenia (ยาลดอาเจียน)', 480.00),
(42, 47, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(43, 48, 'Gabapentin (ยาแก้ปวดเส้นประสาท)', 150.00),
(44, 49, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(45, 50, 'Amoxicillin (ยาปฏิชีวนะ)', 150.00),
(46, 51, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(47, 52, 'Maropitant / Cerenia (ยาลดอาเจียน)', 480.00),
(48, 53, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(49, 54, 'Carprofen (ยาลดอักเสบ/แก้ปวด)', 350.00),
(50, 55, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(51, 56, 'NexGard (ยากำจัดเห็บหมัด)', 450.00),
(52, 57, 'Maropitant / Cerenia (ยาลดอาเจียน)', 480.00),
(53, 58, 'Chlorpheniramine (ยาแก้แพ้)', 50.00),
(54, 59, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(55, 60, 'Furosemide (ยาขับปัสสาวะ)', 100.00),
(56, 61, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(57, 62, 'Chlorpheniramine (ยาแก้แพ้)', 50.00),
(58, 63, 'Metronidazole (ยาแก้ท้องเสีย)', 140.00),
(59, 64, 'Prednisolone (ยาสเตียรอยด์)', 80.00),
(60, 65, 'Tramadol (ยาแก้ปวดรุนแรง)', 120.00),
(61, 66, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(62, 67, 'NexGard (ยากำจัดเห็บหมัด)', 450.00),
(63, 68, 'Amoxicillin (ยาปฏิชีวนะ)', 150.00),
(64, 69, 'Prednisolone (ยาสเตียรอยด์)', 80.00),
(65, 70, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(66, 71, 'Gabapentin (ยาแก้ปวดเส้นประสาท)', 150.00),
(67, 72, 'Enalapril (ยาลดความดัน/โรคหัวใจ)', 180.00),
(68, 73, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(69, 74, 'Metronidazole (ยาแก้ท้องเสีย)', 140.00),
(70, 75, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(71, 76, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(72, 77, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(73, 78, 'Enalapril (ยาลดความดัน/โรคหัวใจ)', 180.00),
(74, 79, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(75, 80, 'Prednisolone (ยาสเตียรอยด์)', 80.00),
(76, 81, 'Amoxicillin (ยาปฏิชีวนะ)', 150.00),
(77, 82, 'Maropitant / Cerenia (ยาลดอาเจียน)', 480.00),
(78, 83, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(79, 84, 'Carprofen (ยาลดอักเสบ/แก้ปวด)', 350.00),
(80, 85, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(81, 86, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(82, 87, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(83, 88, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(84, 89, 'Carprofen (ยาลดอักเสบ/แก้ปวด)', 350.00),
(85, 90, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(86, 91, 'Meloxicam (ยาลดอักเสบ/แก้ปวด)', 220.00),
(87, 92, 'Metronidazole (ยาแก้ท้องเสีย)', 140.00),
(88, 93, 'Chlorpheniramine (ยาแก้แพ้)', 50.00),
(89, 94, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(90, 95, 'Furosemide (ยาขับปัสสาวะ)', 100.00),
(91, 96, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(92, 97, 'Prednisolone (ยาสเตียรอยด์)', 80.00),
(93, 98, 'Maropitant / Cerenia (ยาลดอาเจียน)', 480.00),
(94, 99, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(95, 100, 'Tramadol (ยาแก้ปวดรุนแรง)', 120.00),
(96, 101, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(97, 102, 'NexGard (ยากำจัดเห็บหมัด)', 450.00),
(98, 103, 'Amoxicillin (ยาปฏิชีวนะ)', 150.00),
(99, 104, 'Chlorpheniramine (ยาแก้แพ้)', 50.00),
(100, 105, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(101, 106, 'Gabapentin (ยาแก้ปวดเส้นประสาท)', 150.00),
(102, 107, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(103, 108, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(104, 109, 'Metronidazole (ยาแก้ท้องเสีย)', 140.00),
(105, 110, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(106, 111, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(107, 112, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(108, 113, 'Enalapril (ยาลดความดัน/โรคหัวใจ)', 180.00),
(109, 114, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(110, 115, 'Prednisolone (ยาสเตียรอยด์)', 80.00),
(111, 116, 'Amoxicillin (ยาปฏิชีวนะ)', 150.00),
(112, 117, 'Maropitant / Cerenia (ยาลดอาเจียน)', 480.00),
(113, 118, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(114, 119, 'Carprofen (ยาลดอักเสบ/แก้ปวด)', 350.00),
(115, 120, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(116, 121, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(117, 122, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(118, 123, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(119, 124, 'Meloxicam (ยาลดอักเสบ/แก้ปวด)', 220.00),
(120, 125, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(121, 126, 'Meloxicam (ยาลดอักเสบ/แก้ปวด)', 220.00),
(122, 127, 'Metronidazole (ยาแก้ท้องเสีย)', 140.00),
(123, 128, 'Chlorpheniramine (ยาแก้แพ้)', 50.00),
(124, 129, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(125, 130, 'Furosemide (ยาขับปัสสาวะ)', 100.00),
(126, 131, 'Doxycycline (ยาปฏิชีวนะ)', 180.00),
(127, 132, 'Prednisolone (ยาสเตียรอยด์)', 80.00),
(128, 133, 'Maropitant / Cerenia (ยาลดอาเจียน)', 480.00),
(129, 134, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(130, 135, 'Tramadol (ยาแก้ปวดรุนแรง)', 120.00),
(131, 136, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(132, 137, 'NexGard (ยากำจัดเห็บหมัด)', 450.00),
(133, 138, 'Amoxicillin (ยาปฏิชีวนะ)', 150.00),
(134, 139, 'Chlorpheniramine (ยาแก้แพ้)', 50.00),
(135, 140, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(136, 141, 'Gabapentin (ยาแก้ปวดเส้นประสาท)', 150.00),
(137, 142, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(138, 143, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(139, 144, 'Metronidazole (ยาแก้ท้องเสีย)', 140.00),
(140, 145, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(141, 146, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(142, 147, 'Clavaseptin (ยาปฏิชีวนะ)', 280.00),
(143, 148, 'Enalapril (ยาลดความดัน/โรคหัวใจ)', 180.00),
(144, 149, 'Bravecto (ยากำจัดเห็บหมัด)', 950.00),
(145, 150, 'Prednisolone (ยาสเตียรอยด์)', 80.00),
(146, 150, 'Chlorpheniramine (ยาแก้แพ้)', 50.00),
(147, 149, 'Metronidazole (ยาแก้ท้องเสีย)', 140.00),
(148, 148, 'Furosemide (ยาขับปัสสาวะ)', 100.00),
(149, 147, 'Carprofen (ยาลดอักเสบ/แก้ปวด)', 350.00),
(150, 146, 'Amoxicillin (ยาปฏิชีวนะ)', 150.00),
(151, 151, 'NexGard (ยากำจัดเห็บหมัด)', 450.00),
(152, 151, 'ค่าบริการตรวจรักษา (Service Fee)', 100.00),
(163, 161, 'NexGard (ยากำจัดเห็บหมัด)', 450.00),
(164, 161, 'Meloxicam (ยาลดอักเสบ/แก้ปวด)', 220.00);

-- --------------------------------------------------------

--
-- Table structure for table `treatment`
--

CREATE TABLE `treatment` (
  `treatment_ID` int(9) NOT NULL,
  `medicine_ID` int(4) NOT NULL,
  `appointment_ID` int(9) NOT NULL,
  `treatment_detail` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `treatment`
--

INSERT INTO `treatment` (`treatment_ID`, `medicine_ID`, `appointment_ID`, `treatment_detail`) VALUES
(1, 8, 1, 'Prescribed Bravecto for flea and tick prevention'),
(2, 11, 2, 'Administered Chlorpheniramine for allergy relief'),
(3, 13, 3, 'Prescribed Metronidazole for gastrointestinal issue'),
(4, 9, 4, 'Dispensed NexGard as requested by owner'),
(5, 2, 5, 'Clavaseptin given post-dental scaling'),
(6, 15, 6, 'Started Enalapril for mild hypertension'),
(7, 3, 7, 'Doxycycline drops prescribed for eye infection'),
(8, 4, 8, 'Meloxicam given to reduce ear inflammation'),
(9, 5, 9, 'Carprofen prepared for post-surgery pain management'),
(10, 6, 10, 'Tramadol prescribed for severe leg pain'),
(11, 12, 16, 'Administered Cerenia to stop vomiting'),
(12, 8, 17, 'Routine physical exam and Bravecto given'),
(13, 9, 18, 'NexGard prescribed for 3 months'),
(14, 1, 19, 'Amoxicillin prescribed for skin infection'),
(15, 10, 20, 'Prednisolone given for minor inflammation pre-surgery'),
(16, 2, 21, 'Clavaseptin dispensed after dental cleaning'),
(17, 8, 22, 'Vaccines updated, parasite control provided'),
(18, 3, 23, 'Doxycycline eye drops prescribed'),
(19, 5, 24, 'Carprofen given for mild sprain'),
(20, 14, 25, 'Furosemide dosage adjusted'),
(21, 4, 26, 'Meloxicam prescribed for ear pain'),
(22, 8, 27, 'Microchip inserted, standard care given'),
(23, 13, 28, 'Metronidazole prescribed for 5 days'),
(24, 11, 29, 'Chlorpheniramine given for itching'),
(25, 8, 30, 'General checkup, healthy'),
(26, 15, 31, 'Enalapril started after blood test results'),
(27, 3, 32, 'Doxycycline prescribed for kennel cough'),
(28, 8, 33, 'Rabies vaccine administered'),
(29, 2, 34, 'Clavaseptin prescribed for UTI'),
(30, 6, 35, 'Tramadol given post-neutering'),
(31, 2, 36, 'Antibiotics prescribed for gingivitis'),
(32, 13, 37, 'Mild laxative and Metronidazole given'),
(33, 9, 38, 'NexGard dispensed'),
(34, 4, 39, 'Ear drops and Meloxicam prescribed'),
(35, 3, 40, 'Eye ointment applied in clinic'),
(36, 8, 41, 'Booster vaccines completed'),
(37, 5, 42, 'Wound healing well, Carprofen continued'),
(38, 10, 43, 'Prednisolone prescribed for severe allergy'),
(39, 8, 44, 'Deworming medication administered'),
(40, 8, 45, 'Glands expressed, routine care'),
(41, 12, 46, 'Hairball paste and Cerenia given'),
(42, 3, 47, 'Doxycycline started for suspected tick fever'),
(43, 7, 48, 'Gabapentin prescribed for chronic arthritis pain'),
(44, 8, 49, 'Blood drawn, routine preventatives given'),
(45, 1, 50, 'Amoxicillin and antifungal shampoo prescribed'),
(46, 8, 51, 'Routine physical exam and Bravecto given'),
(47, 12, 52, 'Administered Cerenia to stimulate appetite'),
(48, 8, 53, 'Rabies vaccine administered successfully'),
(49, 5, 54, 'Carprofen given for mild sprain'),
(50, 2, 55, 'Clavaseptin dispensed after dental cleaning'),
(51, 9, 56, 'NexGard prescribed for ear mites control'),
(52, 12, 57, 'Cerenia injection to stop vomiting'),
(53, 11, 58, 'Chlorpheniramine given for skin allergy'),
(54, 8, 59, 'Booster vaccines completed'),
(55, 14, 60, 'Dietary plan and mild Furosemide adjusted'),
(56, 3, 61, 'Doxycycline eye drops prescribed'),
(57, 11, 62, 'Cough syrup and Chlorpheniramine given'),
(58, 13, 63, 'Metronidazole prescribed for 5 days'),
(59, 10, 64, 'Prednisolone given for minor inflammation pre-surgery'),
(60, 6, 65, 'Tramadol given post-neutering'),
(61, 8, 66, 'Microchip inserted, standard care given'),
(62, 9, 67, 'NexGard dispensed for 3 months'),
(63, 1, 68, 'Amoxicillin prescribed for UTI'),
(64, 10, 69, 'Prednisolone given to reduce scratching'),
(65, 8, 70, 'Glands expressed, routine care'),
(66, 7, 71, 'Gabapentin prescribed for chronic arthritis pain'),
(67, 15, 72, 'Enalapril started after blood test results'),
(68, 8, 73, 'Heartworm prevention administered'),
(69, 13, 74, 'Mild laxative and Metronidazole given'),
(70, 8, 75, 'General checkup, healthy'),
(71, 2, 76, 'Antibiotics prescribed for bad breath/gingivitis'),
(72, 2, 77, 'Clavaseptin prescribed for gums'),
(73, 15, 78, 'Enalapril dosage adjusted for senior pet'),
(74, 8, 79, 'Deworming medication administered'),
(75, 10, 80, 'Prednisolone prescribed for severe allergy'),
(76, 1, 81, 'Amoxicillin and antifungal shampoo prescribed'),
(77, 12, 82, 'Hairball paste and Cerenia given'),
(78, 3, 83, 'Doxycycline started for suspected tick fever'),
(79, 5, 84, 'Wound healing well, Carprofen continued'),
(80, 3, 85, 'Eye ointment applied in clinic'),
(81, 8, 86, 'Routine physical exam and Bravecto given'),
(82, 8, 87, 'Vaccines updated, parasite control provided'),
(83, 8, 88, 'Rabies vaccine administered'),
(84, 5, 89, 'Carprofen prescribed for joint pain'),
(85, 2, 90, 'Clavaseptin dispensed after scaling'),
(86, 4, 91, 'Ear drops and Meloxicam prescribed'),
(87, 13, 92, 'Metronidazole prescribed for diarrhea'),
(88, 11, 93, 'Chlorpheniramine given for itching'),
(89, 8, 94, 'Booster vaccines completed'),
(90, 14, 95, 'Weight management progressing well'),
(91, 3, 96, 'Doxycycline eye drops prescribed'),
(92, 10, 97, 'Prednisolone given for asthma'),
(93, 12, 98, 'Cerenia and IV fluids given'),
(94, 8, 99, 'Pre-op blood test and general check'),
(95, 6, 100, 'Tramadol prescribed for post-op pain'),
(96, 8, 101, 'Microchip implanted successfully'),
(97, 9, 102, 'NexGard dispensed'),
(98, 1, 103, 'Amoxicillin continued for UTI'),
(99, 11, 104, 'Chlorpheniramine given for skin allergy'),
(100, 8, 105, 'Anal glands expressed'),
(101, 7, 106, 'Gabapentin prescribed for joint pain'),
(102, 8, 107, 'Blood drawn, routine preventatives given'),
(103, 8, 108, 'Heartworm test negative, preventative given'),
(104, 13, 109, 'Mild laxative given'),
(105, 8, 110, 'General checkup, healthy'),
(106, 2, 111, 'Clavaseptin for minor gum inflammation'),
(107, 2, 112, 'Antibiotics prescribed for tartar'),
(108, 15, 113, 'Senior care, Enalapril given'),
(109, 8, 114, 'Dewormer for tapeworm given'),
(110, 10, 115, 'Prednisolone prescribed for allergies'),
(111, 1, 116, 'Antifungal cream and Amoxicillin'),
(112, 12, 117, 'Hairball paste prescribed'),
(113, 3, 118, 'Doxycycline for Tick Fever'),
(114, 5, 119, 'Carprofen for post-op recovery'),
(115, 3, 120, 'Eye drops for ulcer'),
(116, 8, 121, 'Wellness exam clear'),
(117, 8, 122, 'Annual vaccines done'),
(118, 8, 123, 'Rabies shot given'),
(119, 4, 124, 'Meloxicam given for limping'),
(120, 2, 125, 'Clavaseptin post-dental'),
(121, 4, 126, 'Ear cleaner and Meloxicam'),
(122, 13, 127, 'Metronidazole for chronic diarrhea'),
(123, 11, 128, 'Chlorpheniramine for skin rash'),
(124, 8, 129, 'Booster shots updated'),
(125, 14, 130, 'Diet checked, steady weight loss'),
(126, 3, 131, 'Doxycycline drops'),
(127, 10, 132, 'Asthma inhaler prescribed'),
(128, 12, 133, 'Cerenia injection'),
(129, 8, 134, 'Ready for spay next week'),
(130, 6, 135, 'Tramadol post-surgery'),
(131, 8, 136, 'Microchip inserted'),
(132, 9, 137, 'NexGard given'),
(133, 1, 138, 'Amoxicillin for UTI'),
(134, 11, 139, 'Skin scrape done, Chlorpheniramine given'),
(135, 8, 140, 'Glands cleared'),
(136, 7, 141, 'Gabapentin for arthritis'),
(137, 8, 142, 'Blood panel normal'),
(138, 8, 143, 'Heartworm med given'),
(139, 13, 144, 'Laxative given for constipation'),
(140, 8, 145, 'Checkup clear'),
(141, 2, 146, 'Clavaseptin for bad breath'),
(142, 2, 147, 'Gingivitis treatment'),
(143, 15, 148, 'Senior blood work, Enalapril started'),
(144, 8, 149, 'Dewormer given'),
(145, 10, 150, 'Prednisolone for allergy flare-up'),
(146, 11, 150, 'Additional Chlorpheniramine prescribed for itchiness'),
(147, 13, 149, 'Metronidazole also given to prevent diarrhea'),
(148, 14, 148, 'Furosemide added for fluid retention'),
(149, 5, 147, 'Carprofen prescribed for minor pain'),
(150, 1, 146, 'Amoxicillin course added for suspected infection'),
(161, 9, 160, 'ทดสอบ'),
(162, 4, 160, 'ทดสอบ');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointments_ID`),
  ADD KEY `doctor_ID` (`doctor_ID`),
  ADD KEY `pet_ID` (`pet_ID`);

--
-- Indexes for table `breed`
--
ALTER TABLE `breed`
  ADD PRIMARY KEY (`breed_ID`);

--
-- Indexes for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD PRIMARY KEY (`diagnoses_ID`),
  ADD KEY `appointment_ID` (`appointment_ID`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`doctor_ID`);

--
-- Indexes for table `medication`
--
ALTER TABLE `medication`
  ADD PRIMARY KEY (`medicine_ID`);

--
-- Indexes for table `owner`
--
ALTER TABLE `owner`
  ADD PRIMARY KEY (`owner_ID`);

--
-- Indexes for table `pet`
--
ALTER TABLE `pet`
  ADD PRIMARY KEY (`pet_ID`),
  ADD KEY `owner_ID` (`owner_ID`),
  ADD KEY `breed_ID` (`breed_ID`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`receipt_ID`),
  ADD KEY `receipts_ibfk_1` (`appointment_ID`);

--
-- Indexes for table `receipt_details`
--
ALTER TABLE `receipt_details`
  ADD PRIMARY KEY (`detail_ID`),
  ADD KEY `receipt_details_ibfk_1` (`receipt_ID`);

--
-- Indexes for table `treatment`
--
ALTER TABLE `treatment`
  ADD PRIMARY KEY (`treatment_ID`),
  ADD KEY `appointment_ID` (`appointment_ID`),
  ADD KEY `medicine_ID` (`medicine_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointments_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `diagnoses`
--
ALTER TABLE `diagnoses`
  MODIFY `diagnoses_ID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `doctor_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `owner`
--
ALTER TABLE `owner`
  MODIFY `owner_ID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `pet`
--
ALTER TABLE `pet`
  MODIFY `pet_ID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `receipt_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT for table `receipt_details`
--
ALTER TABLE `receipt_details`
  MODIFY `detail_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `treatment`
--
ALTER TABLE `treatment`
  MODIFY `treatment_ID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`doctor_ID`) REFERENCES `doctor` (`doctor_ID`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`pet_ID`) REFERENCES `pet` (`pet_ID`) ON DELETE CASCADE;

--
-- Constraints for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD CONSTRAINT `diagnoses_ibfk_1` FOREIGN KEY (`appointment_ID`) REFERENCES `appointments` (`appointments_ID`) ON DELETE CASCADE;

--
-- Constraints for table `pet`
--
ALTER TABLE `pet`
  ADD CONSTRAINT `pet_ibfk_1` FOREIGN KEY (`owner_ID`) REFERENCES `owner` (`owner_ID`),
  ADD CONSTRAINT `pet_ibfk_2` FOREIGN KEY (`breed_ID`) REFERENCES `breed` (`breed_ID`);

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `fk_receipt_appointment` FOREIGN KEY (`appointment_ID`) REFERENCES `appointments` (`appointments_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`appointment_ID`) REFERENCES `appointments` (`appointments_ID`) ON DELETE CASCADE;

--
-- Constraints for table `receipt_details`
--
ALTER TABLE `receipt_details`
  ADD CONSTRAINT `fk_detail_receipt` FOREIGN KEY (`receipt_ID`) REFERENCES `receipts` (`receipt_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `receipt_details_ibfk_1` FOREIGN KEY (`receipt_ID`) REFERENCES `receipts` (`receipt_ID`) ON DELETE CASCADE;

--
-- Constraints for table `treatment`
--
ALTER TABLE `treatment`
  ADD CONSTRAINT `treatment_ibfk_1` FOREIGN KEY (`appointment_ID`) REFERENCES `appointments` (`appointments_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `treatment_ibfk_3` FOREIGN KEY (`medicine_ID`) REFERENCES `medication` (`medicine_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
