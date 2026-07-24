-- Insert sample hotels with images
INSERT INTO hotels (destination_id, name, address, city, country, description, amenities, star_rating, image) VALUES
(1, 'The Bengal Palace Hotel', 'Plot 23, Road 27, Gulshan-1', 'Dhaka', 'Bangladesh', 
'Experience luxury in the heart of Dhaka. The Bengal Palace Hotel offers world-class amenities, exceptional service, and stunning contemporary architecture. Perfect for both business and leisure travelers.', 
'Free WiFi,Swimming Pool,Fitness Center,Restaurant,Bar,Room Service,Spa,Business Center,Parking,Airport Shuttle', 
5.0, 'assets/images/dhaka_hotel_luxury.png'),

(2, 'Cox\'s Bazar Beach Resort', 'Hotel Motel Zone, Kolatoli Road', 'Cox\'s Bazar', 'Bangladesh', 
'Nestled along the world\'s longest natural sea beach, our resort offers a perfect blend of relaxation and adventure. Wake up to breathtaking ocean views and enjoy beachfront luxury.', 
'Beachfront,Free WiFi,Swimming Pool,Restaurant,Bar,Water Sports,Spa,Kids Club,Parking,Beach Access', 
4.5, 'assets/images/coxs_bazar_beach_resort.png'),

(3, 'Sylhet Tea Garden Resort', 'Sreemangal, Moulvibazar Road', 'Sylhet', 'Bangladesh', 
'Escape to tranquility surrounded by lush tea gardens and rolling hills. Our eco-resort offers a unique experience combining nature, comfort, and authentic Bengali hospitality.', 
'Free WiFi,Restaurant,Tea Garden Tours,Nature Walks,Organic Food,Yoga,Bird Watching,Parking,Airport Shuttle', 
4.0, 'assets/images/sylhet_tea_garden_resort.png'),

(4, 'Al-Masaah Grand Hotel', 'Sheikh Zayed Road, Downtown', 'Dubai', 'United Arab Emirates', 
'Indulge in Arabian luxury at its finest. Our 5-star hotel features opulent rooms, world-class dining, and proximity to Dubai\'s most iconic attractions. Experience true Arabian hospitality.', 
'Free WiFi,Infinity Pool,Spa,Fine Dining,Bar,Concierge,Valet Parking,Airport Limousine,Fitness Center,Business Center', 
5.0, 'assets/images/dubai_luxury_hotel.png'),

(5, 'Bangkok Heritage Boutique', 'Sukhumvit Road, Asok', 'Bangkok', 'Thailand', 
'A perfect fusion of traditional Thai elegance and modern luxury. Located in the heart of Bangkok with a stunning rooftop pool overlooking the vibrant city skyline.', 
'Rooftop Pool,Free WiFi,Thai Restaurant,Bar,Massage,Fitness Center,Concierge,Business Center,Parking', 
4.5, 'assets/images/bangkok_boutique_hotel.png'),

(6, 'Marina Bay Tower Hotel', '10 Bayfront Avenue, Marina Bay', 'Singapore', 'Singapore', 
'Ultra-modern luxury hotel with breathtaking Marina Bay views. Experience cutting-edge design, exceptional service, and proximity to Singapore\'s premier shopping and entertainment districts.', 
'Free WiFi,Infinity Pool,Spa,Fine Dining,Bar,Business Center,Concierge,Valet Parking,Fitness Center,Sky Lounge', 
5.0, 'assets/images/singapore_modern_hotel.png');

-- Insert sample rooms for each hotel
-- The Bengal Palace Hotel (Hotel ID: 1)
INSERT INTO rooms (hotel_id, room_type, description, price_per_night, max_guests, bed_type, room_size, amenities, total_rooms, available_rooms, image) VALUES
(1, 'Deluxe Room', 'Spacious room with modern amenities and city views', 8500.00, 2, 'King Bed', '35 sqm', 'AC,TV,Mini Bar,Safe,Coffee Maker,Balcony', 20, 20, 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800'),
(1, 'Executive Suite', 'Luxury suite with separate living area and premium amenities', 15000.00, 3, 'King Bed + Sofa Bed', '65 sqm', 'AC,TV,Mini Bar,Safe,Coffee Maker,Living Room,Work Desk,Bathtub', 10, 10, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800'),
(1, 'Twin Room', 'Comfortable room with two beds, perfect for friends or colleagues', 7500.00, 2, 'Twin Beds', '30 sqm', 'AC,TV,Mini Bar,Safe,Coffee Maker', 15, 15, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800');

-- Cox's Bazar Beach Resort (Hotel ID: 2)
INSERT INTO rooms (hotel_id, room_type, description, price_per_night, max_guests, bed_type, room_size, amenities, total_rooms, available_rooms, image) VALUES
(2, 'Ocean View Room', 'Stunning sea views with private balcony overlooking the beach', 6500.00, 2, 'Queen Bed', '32 sqm', 'AC,TV,Mini Bar,Balcony,Beach View,Coffee Maker', 25, 25, 'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=800'),
(2, 'Beach Villa', 'Luxurious beachfront villa with direct beach access', 12000.00, 4, 'King Bed + Twin Beds', '80 sqm', 'AC,TV,Mini Bar,Living Room,Kitchenette,Beach Access,Jacuzzi', 8, 8, 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=800'),
(2, 'Family Room', 'Spacious room ideal for families with children', 8000.00, 4, 'King Bed + Bunk Beds', '45 sqm', 'AC,TV,Mini Bar,Safe,Kids Amenities', 12, 12, 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?w=800');

-- Sylhet Tea Garden Resort (Hotel ID: 3)
INSERT INTO rooms (hotel_id, room_type, description, price_per_night, max_guests, bed_type, room_size, amenities, total_rooms, available_rooms, image) VALUES
(3, 'Garden View Cottage', 'Cozy cottage surrounded by tea gardens with panoramic views', 4500.00, 2, 'Queen Bed', '28 sqm', 'AC,TV,Coffee Maker,Balcony,Garden View', 15, 15, 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?w=800'),
(3, 'Premium Suite', 'Spacious suite with living area and tea garden views', 7000.00, 3, 'King Bed', '50 sqm', 'AC,TV,Mini Bar,Living Room,Work Desk,Garden View,Balcony', 8, 8, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800');

-- Al-Masaah Grand Hotel Dubai (Hotel ID: 4)
INSERT INTO rooms (hotel_id, room_type, description, price_per_night, max_guests, bed_type, room_size, amenities, total_rooms, available_rooms, image) VALUES
(4, 'Luxury Room', 'Elegantly appointed room with Arabian-inspired decor', 25000.00, 2, 'King Bed', '40 sqm', 'AC,TV,Mini Bar,Safe,Coffee Maker,Work Desk,Marble Bathroom', 30, 30, 'https://images.unsplash.com/photo-1591088398332-8a7791972843?w=800'),
(4, 'Royal Suite', 'Opulent suite with separate living and dining areas', 45000.00, 4, 'King Bed + Sofa Bed', '90 sqm', 'AC,TV,Mini Bar,Living Room,Dining Area,Butler Service,Jacuzzi,City View', 12, 12, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800');

-- Bangkok Heritage Boutique (Hotel ID: 5)
INSERT INTO rooms (hotel_id, room_type, description, price_per_night, max_guests, bed_type, room_size, amenities, total_rooms, available_rooms, image) VALUES
(5, 'Superior Room', 'Modern room with Thai design elements and city views', 5500.00, 2, 'King Bed', '30 sqm', 'AC,TV,Mini Bar,Safe,Coffee Maker,City View', 20, 20, 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=800'),
(5, 'Pool Suite', 'Luxury suite with direct pool access from private terrace', 12000.00, 3, 'King Bed', '55 sqm', 'AC,TV,Mini Bar,Living Area,Pool Access,Bathtub,Balcony', 6, 6, 'https://images.unsplash.com/photo-1560184897-ae75f418493e?w=800');

-- Marina Bay Tower Hotel Singapore (Hotel ID: 6)
INSERT INTO rooms (hotel_id, room_type, description, price_per_night, max_guests, bed_type, room_size, amenities, total_rooms, available_rooms, image) VALUES
(6, 'Deluxe Marina Room', 'Contemporary room with stunning Marina Bay views', 18000.00, 2, 'King Bed', '38 sqm', 'AC,TV,Mini Bar,Safe,Coffee Maker,Bay View,Work Desk', 25, 25, 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?w=800'),
(6, 'Sky Suite', 'Luxurious high-floor suite with panoramic city and bay views', 35000.00, 4, 'King Bed + Sofa Bed', '85 sqm', 'AC,TV,Mini Bar,Living Room,Dining Area,Bathtub,Floor-to-Ceiling Windows,Sky View', 10, 10, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800');
