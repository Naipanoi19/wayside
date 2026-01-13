-- Wayside Airbnb Management System - PostgreSQL Database Schema
-- Created for Final Year University Project

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS payments CASCADE;
DROP TABLE IF EXISTS bookings CASCADE;
DROP TABLE IF EXISTS guests CASCADE;
DROP TABLE IF EXISTS rooms CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Users table (for authentication and user management)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('admin', 'staff', 'guest')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rooms table (room inventory)
CREATE TABLE rooms (
    id SERIAL PRIMARY KEY,
    room_number VARCHAR(20) UNIQUE NOT NULL,
    room_type VARCHAR(50) NOT NULL CHECK (room_type IN ('Single', 'Double', 'Suite')),
    price_per_night DECIMAL(10, 2) NOT NULL CHECK (price_per_night > 0),
    description TEXT,
    amenities TEXT,
    photo VARCHAR(255),
    status VARCHAR(20) DEFAULT 'available' CHECK (status IN ('available', 'booked', 'cleaning')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Guests table (for walk-ins and guest information)
CREATE TABLE guests (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    id_number VARCHAR(50),
    phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings table (reservations)
CREATE TABLE bookings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    room_id INTEGER REFERENCES rooms(id) ON DELETE RESTRICT,
    guest_id INTEGER REFERENCES guests(id) ON DELETE SET NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    booking_status VARCHAR(20) DEFAULT 'pending' CHECK (booking_status IN ('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled')),
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT valid_dates CHECK (check_out > check_in)
);

-- Payments table (payment records)
CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    booking_id INTEGER REFERENCES bookings(id) ON DELETE CASCADE,
    amount_paid DECIMAL(10, 2) NOT NULL CHECK (amount_paid > 0),
    payment_method VARCHAR(50) NOT NULL CHECK (payment_method IN ('cash', 'M-Pesa', 'card')),
    payment_ref VARCHAR(255),
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better query performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_rooms_status ON rooms(status);
CREATE INDEX idx_rooms_type ON rooms(room_type);
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_room_id ON bookings(room_id);
CREATE INDEX idx_bookings_dates ON bookings(check_in, check_out);
CREATE INDEX idx_bookings_status ON bookings(booking_status);
CREATE INDEX idx_payments_booking_id ON payments(booking_id);

-- Create a function to check for overlapping bookings
CREATE OR REPLACE FUNCTION check_booking_overlap(
    p_room_id INTEGER,
    p_check_in DATE,
    p_check_out DATE,
    p_booking_id INTEGER DEFAULT NULL
) RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1
        FROM bookings
        WHERE room_id = p_room_id
          AND booking_status NOT IN ('cancelled', 'checked_out')
          AND (p_booking_id IS NULL OR id != p_booking_id)
          AND (
              (check_in <= p_check_in AND check_out > p_check_in) OR
              (check_in < p_check_out AND check_out >= p_check_out) OR
              (check_in >= p_check_in AND check_out <= p_check_out)
          )
    );
END;
$$ LANGUAGE plpgsql;

