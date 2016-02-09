package dbtLab3;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

/**
 * Database is a class that specifies the interface to the movie database. Uses
 * JDBC and the MySQL Connector/J driver.
 */
public class Database {
	/**
	 * The database connection.
	 */
	private Connection conn;

	/**
	 * Create the database interface object. Connection to the database is
	 * performed later.
	 */
	public Database() {
		conn = null;
	}

	/**
	 * Open a connection to the database, using the specified user name and
	 * password.
	 * 
	 * @param userName
	 *            The user name.
	 * @param password
	 *            The user's password.
	 * @return true if the connection succeeded, false if the supplied user name
	 *         and password were not recognized. Returns false also if the JDBC
	 *         driver isn't found.
	 */
	public boolean openConnection(String userName, String password) {
		try {
			Class.forName("com.mysql.jdbc.Driver");
			conn = DriverManager.getConnection("jdbc:mysql://puccini.cs.lth.se/" + "db142", "db142", "classic");
		} catch (SQLException e) {
			e.printStackTrace();
			return false;
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
			return false;
		}
		return true;
	}

	/**
	 * Close the connection to the database.
	 */
	public void closeConnection() {
		try {
			if (conn != null) {
				conn.close();
			}
		} catch (SQLException e) {
		}
		conn = null;
	}

	/**
	 * Check if the connection to the database has been established
	 * 
	 * @return true if the connection has been established
	 */
	public boolean isConnected() {
		return conn != null;
	}

	/* --- insert own code here --- */

	public void loginUser(String username) {
		PreparedStatement statement = null;
		try {
			String sql = "SELECT username, name, address, phoneNbr " + "FROM users " + "WHERE username = ?";
			statement = conn.prepareStatement(sql);
			statement.setString(1, username);
			ResultSet result = statement.executeQuery();
			if (result.next()) {
				username = result.getString("username");
				CurrentUser.instance().loginAs(username);
				System.out.println("User: " + username + " has logged in succesfully");
			} else {
				System.out.println("No such user");
			}

		} catch (SQLException e) {
			e.printStackTrace();
		} finally {
			try {
				statement.close();
			} catch (SQLException e) {
				e.printStackTrace();
			}
		}
	}

	public ArrayList<String> getMovies() {
		PreparedStatement statement = null;
		ArrayList<String> movies = new ArrayList<String>();

		try {
			String sql = "SELECT name " + "FROM movies";
			statement = conn.prepareStatement(sql);
			ResultSet result = statement.executeQuery();
			while (result.next()) {
				movies.add(result.getString("name"));
			}
			return movies;

		} catch (SQLException e) {
			e.printStackTrace();
			return null;
		} finally {
			try {
				statement.close();
			} catch (SQLException e) {
				e.printStackTrace();
			}
		}
	}

	public ArrayList<Map<String, String>> getPerformances(String movieName) {

		PreparedStatement statement = null;
		ArrayList<Map<String, String>> performances = new ArrayList<Map<String, String>>();

		try {
			String sql = "SELECT movieName, date, theaterName, seatsLeft " + " FROM performances "
					+ "WHERE movieName = ?";
			statement = conn.prepareStatement(sql);
			statement.setString(1, movieName);
			ResultSet result = statement.executeQuery();
			while (result.next()) {
				Map<String, String> p = new HashMap<String, String>();
				p.put("movieName", result.getString("movieName"));
				p.put("date", result.getString("date"));
				p.put("theaterName", result.getString("theaterName"));
				p.put("seatsLeft", result.getString("seatsLeft"));
				performances.add(p);

			}
			return performances;

		} catch (SQLException e) {
			e.printStackTrace();
			return null;

		} finally {
			try {
				statement.close();
			} catch (SQLException e) {
				e.printStackTrace();
			}
		}

	}

	public void updateAvailableSeats(String movieName, String date) {

		PreparedStatement statement = null;
		try {
			String sql = "UPDATE performances SET seatsLeft = seatsLeft - 1" + " WHERE movieName = ? AND date = ?";
			statement = conn.prepareStatement(sql);
			statement.setString(1, movieName);
			statement.setString(2, date);
			statement.executeUpdate();
			System.out.println("Update seats!");
			statement.close();

		} catch (SQLException e) {
			e.printStackTrace();

		} finally {
			try {
				statement.close();
			} catch (SQLException e) {
				e.printStackTrace();
			}

		}
	}

	public boolean doReservation(String movieName, String date) {

		PreparedStatement statement = null;
		try {

			conn.setAutoCommit(false); // start transaction

			String sql = "INSERT INTO reservations(date, movieName, userName) " + "VALUES (?, ?, ?)";
			statement = conn.prepareStatement(sql);
			statement.setString(1, date);
			statement.setString(2, movieName);
			statement.setString(3, CurrentUser.instance().getCurrentUserId());
			statement.executeUpdate();
			statement.close();

			sql = "SELECT performances.seatsLeft AS availableSeats " + "FROM performances LEFT JOIN reservations ON "
					+ "(performances.movieName = reservations.movieName AND performances.date = reservations.date) "
					+ "WHERE performances.movieName = ? AND performances.date = ? FOR UPDATE";

			statement = conn.prepareStatement(sql);
			statement.setString(1, movieName);
			statement.setString(2, date);
			ResultSet result = statement.executeQuery();

			if (result.next()) {
				System.out.println("seatsLeft: " + result.getInt("availableSeats"));
				if (result.getInt("availableSeats") <= 0) {
					System.out.println("No seats available!");
					conn.rollback();
					return false;
				} else {
					System.out.println("Seat booked!");
					conn.commit();
					return true;
				}
			}

			statement.close();

		} catch (SQLException e) {
			e.printStackTrace();
			try {
				conn.rollback();
			} catch (SQLException e1) {
				e1.printStackTrace();
			}
		} finally {
			try {
				statement.close();
				conn.setAutoCommit(true);
			} catch (SQLException e) {
				e.printStackTrace();

			}
		}
		return false;

	}

}
