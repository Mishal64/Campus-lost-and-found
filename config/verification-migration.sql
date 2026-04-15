ALTER TABLE items
ADD COLUMN verification_question_1 VARCHAR(255) DEFAULT NULL AFTER image,
ADD COLUMN verification_question_2 VARCHAR(255) DEFAULT NULL AFTER verification_question_1,
ADD COLUMN proof_instructions VARCHAR(255) DEFAULT NULL AFTER verification_question_2;

ALTER TABLE claims
ADD COLUMN verification_answer_1 TEXT NULL AFTER message,
ADD COLUMN verification_answer_2 TEXT NULL AFTER verification_answer_1,
ADD COLUMN proof_file VARCHAR(255) DEFAULT NULL AFTER verification_answer_2;
