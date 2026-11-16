USE digital_police_ob;

INSERT INTO officers (service_number, full_name, `rank`, role, password_hash) VALUES
('AP001','Sgt. James Mwangi','Sergeant','officer','$2y$10$0aW5EmhaK93v.nCH8MC0tOEngxVddkqMfm20YcEKymqsnNv4PSvqK'),
('AP002','Cpl. Mary Wanjiku','Corporal','officer','$2y$10$.FF0fpOW8hQTkUPY6PCzFeNezfZhq067WIs7nLHTqWAgFCrDuhST6'),
('AP003','Insp. David Ochieng','Inspector','admin','$2y$10$3heKzE99IiF9Ggi6cCAYG.wuwW9rW4Se9FUJEM9E3ELO.Fo3MMtI.');

INSERT INTO incident_types (type_name, category) VALUES
('Theft','Property'),('Lost Item','Property'),('Assault','Violence'),('Traffic Accident','Traffic'),('Missing Person','Welfare');

INSERT INTO locations (location_name) VALUES ('Central'),('Kilimani'),('Kasarani'),('Langata Road'),('Karen');

INSERT INTO ob_entries (srs_number, entry_datetime, incident_type_id, location_id, recorded_by, complainant_name, complainant_id_no, complainant_phone, narrative, status) VALUES
('OB/2026/03/001','2026-03-03 08:30',1,1,1,'John Kamau',   '12345678','0700000001','Complainant reports a phone stolen at a bus stage.','Open'),
('OB/2026/03/002','2026-03-03 10:15',2,2,1,'Grace Akinyi', '23456789','0700000002','Complainant reports a lost handbag.','Open'),
('OB/2026/03/003','2026-03-02 22:45',3,3,2,'Peter Njoroge','34567890','0700000003','Complainant reports an assault outside a shop.','Under Investigation');

INSERT INTO ob_actions (entry_id, action_by, action_description) VALUES
(3, 2, 'Statement recorded; suspect to be traced.');