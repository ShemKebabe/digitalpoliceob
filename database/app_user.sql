DROP USER IF EXISTS 'ob_app'@'localhost';
CREATE USER 'ob_app'@'localhost' IDENTIFIED BY 'CHANGE_ME';

GRANT SELECT                 ON digital_police_ob.incident_types  TO 'ob_app'@'localhost';
GRANT SELECT                 ON digital_police_ob.locations       TO 'ob_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON digital_police_ob.officers        TO 'ob_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON digital_police_ob.ob_entries      TO 'ob_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON digital_police_ob.public_reports  TO 'ob_app'@'localhost';

GRANT SELECT, INSERT         ON digital_police_ob.ob_actions      TO 'ob_app'@'localhost';
GRANT SELECT, INSERT         ON digital_police_ob.ob_amendments   TO 'ob_app'@'localhost';
GRANT SELECT, INSERT         ON digital_police_ob.audit_log       TO 'ob_app'@'localhost';