<?php

/**
 * Test the WCS_Retry class's public methods
 */
class WCSR_Resource_Test extends WCSR_Unit_TestCase {

	protected static $from_timestamp;

	protected static $to_timestamp;

	public static function setUpBeforeClass() {
		self::$from_timestamp = strtotime( '2017-09-14 09:13:14' );
		self::$to_timestamp   = strtotime( '2017-10-14 09:14:02' );
	}

	/**
	 * Provide data to test days active
	 */
	public function provider_get_days_active() {

		return array(

			/*
			 * Simulate a new resource that is active for only the first week during its first cycle.
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created
			 * 2. active for at least 1 second more than 6 * 24 * 60, then deactivated
			 */
			0 => array(
				'date_created'         => '2017-09-14 09:13:14', // same as $from_timestamp
				'activation_times'     => array( '2017-09-14 09:13:14' ), // same as $from_timestamp
				'deactivation_times'   => array( '2017-09-20 11:01:40' ),
				'expected_days_active' => 7,
			),

			/*
			 * Simulate an existing active resource that is active for 10 days at the start of its 2nd cycle.
			 *
			 * To test this requires a resource that is:
			 * 0. created prior to the start of the period being checked ($creation_time < $from_timestamp)
			 * 1. activate at the start of the period being checked
			 * 2. active for at least 1 second more than 9 * 24 * 60 during the period then deactivated
			 */
			1 => array(
				'date_created'         => '2017-08-14 09:13:14', // 1 month prior to $from_timestamp
				'activation_times'     => array( '2017-08-14 09:13:14' ),
				'deactivation_times'   => array( '2017-09-23 11:13:40' ),
				'expected_days_active' => 10,
			),

			/*
			 * Simulate an existing inactive resource that is active for 10 days in the middle of its 2nd cycle.
			 *
			 * To test this requires a resource that is:
			 * 0. created prior to the start of the period being checked ($creation_time < $from_timestamp)
			 * 1. first activation timestamp after the start time of the period being checked ($activation_times[0] > $from_timestamp)
			 * 2. active for at least 1 second more than 9 * 24 * 60 during the period then deactivated
			 */
			2 => array(
				'date_created'         => '2017-08-14 09:13:14', // 1 month prior to $from_timestamp
				'activation_times'     => array( '2017-09-24 09:13:14' ), // 10 days after $from_timestamp
				'deactivation_times'   => array( '2017-10-03 11:13:40' ), // 9 days, 2 hours, 26 seconds after activation timestamp
				'expected_days_active' => 10,
			),

			/*
			 * Simulate a new resource that is active for multiple different periods during its first cycle with a total of 10 days.
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created
			 * 2. active for 2 days, then deactivated for 2 days
			 * 3. active for 2 days, then deactivated for 2 days
			 * 4. active for 2 days, then deactivated for 2 days
			 * 5. active for 2 days, then deactivated for 2 days
			 * 6. activated again for the rest of the cycle
			 */
			3 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-18 09:13:14', '2017-09-22 09:13:14', '2017-10-26 09:13:14', '2017-10-30 09:14:02' ),
				'deactivation_times'   => array( '2017-09-16 09:13:13', '2017-09-20 09:13:13', '2017-09-24 09:13:13', '2017-10-28 09:13:13' ),
				'expected_days_active' => 6,
			),

			/*
			 * Simulate a new resource that is active for multiple different periods during its first cycle with a total of 10 days.
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created
			 * 2. active for at least 1 second more than 4 * 24 * 60, then deactivated
			 * 3. actived again for at least 1 second more than 4 * 24 * 60, then deactivated before the end of the cycle ($to_timestamp)
			 */
			4 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-20 09:13:14' ),
				'deactivation_times'   => array( '2017-09-18 09:13:15', '2017-09-24 09:13:15' ),
				'expected_days_active' => 10,
			),

			/*
			 * Simulate an existing active resource that is active for multiple different occasions during its 2nd cycle for a total of 10 days.
			 *
			 * To test this requires a resource that is:
			 * 0. created prior to the start of the period being checked ($creation_time < $from_timestamp)
			 * 1. active at the start of the period being checked
			 * 2. active for at least 1 second more than 4 * 24 * 60, then deactivated
			 * 3. activated again for at least 1 second more than 4 * 24 * 60, then deactivated before the end of the cycle ($to_timestamp)
			 */
			5 => array(
				'date_created'         => '2017-08-14 09:13:14', // 1 month prior to $from_timestamp
				'activation_times'     => array( '2017-09-30 09:13:14' ), // previously activated in the last cycle
				'deactivation_times'   => array( '2017-09-18 10:14:15', '2017-10-04 12:24:10' ),
				'expected_days_active' => 10,
			),

			/*
			 * Simulate an existing inactive resource that is actived for multiple different occasions during its 2nd cycle for a total of 10 days.
			 *
			 * To test this requires a resource that is:
			 * 0. created prior to the start of the period being checked ($creation_time < $from_timestamp)
			 * 1. inactive at the start of the period being checked
			 * 2. activated for at least 1 second more than 4 * 24 * 60, then deactivated more than 5 * 24 * 60 before the end of the cycle ($to_timestamp)
			 * 3. activated again for at least 1 second more than 4 * 24 * 60, then deactivated before the end of the cycle ($to_timestamp)
			 */
			6 => array(
				'date_created'         => '2017-08-14 09:13:14', // 1 month prior to $from_timestamp
				'activation_times'     => array( '2017-09-26 09:13:14', '2017-10-05 09:13:14' ), // previously activated in the last cycle
				'deactivation_times'   => array( '2017-09-30 15:35:43', '2017-10-09 12:24:10' ),
				'expected_days_active' => 10,
			),

			/*
			 * Simulate a new active resource that is activated and deactivated for multiple occasions on the same day.
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created
			 * 2. activated for just over an hour, then deactivated for 2'ish hours
			 * 3. activated 4 hours then deactivated for the rest of the cycle
			 */
			7 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-14 13:13:14' ),
				'deactivation_times'   => array( '2017-09-14 10:35:43', '2017-09-14 17:24:10' ),
				'expected_days_active' => 1,
			),

			/*
			 * Simulate a new active resource that is active for the full cycle
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 */
			8 => array(
				'date_created'         => '2017-09-14 09:13:14', // same as $from_timestamp
				'activation_times'     => array( '2017-09-14 09:13:14' ),
				'deactivation_times'   => array(),
				'expected_days_active' => 31,
			),

			/*
			 * Simulate a resource that has never been activated or deactivated
			 *
			 * To test this requires a resource that is:
			 * 0. created prior to the start of the period being checked ($creation_time < $from_timestamp)
			 * 1. active before the start of the period being checked
			 */
			9 => array(
				'date_created'         => '2017-08-14 09:13:14',
				'activation_times'     => array(),
				'deactivation_times'   => array(),
				'expected_days_active' => 0,
			),

			/*
			 * Simulate a new active resource that is activated and deactivated for multiple occasions on the same day and then left active for 2 days.
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created
			 * 2. activated for just over an hour, then deactivated for 2'ish hours
			 * 3. activated 4 hours then deactivated for the rest of the cycle
			 */
			10 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-14 13:13:14', '2017-09-14 20:00:03' ),
				'deactivation_times'   => array( '2017-09-14 10:35:43', '2017-09-14 17:24:10', '2017-09-16 17:24:10' ),
				'expected_days_active' => 3,
			),

			/*
			 * Simulate a new active resource that is activated and deactivated for multiple occasions on the same day and then left active for the rest of the month.
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created
			 * 2. activated for just over an hour, then deactivated for 2'ish hours
			 * 3. activated for the rest of the cycle
			 */
			11 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-14 13:13:14', '2017-09-14 20:00:03' ),
				'deactivation_times'   => array( '2017-09-14 10:35:43', '2017-09-14 17:24:10' ),
				'expected_days_active' => 31,
			),

			/*
			 * Simulate a new active resource that is activated and deactivated for multiple occasions on the same day and then left inactive for the rest of the month.
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created
			 * 2. activated for just over an hour, then deactivated for 2'ish hours
			 * 3. activated 4 hours then deactivated for the rest of the period
			 */
			12 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-14 13:13:14' ),
				'deactivation_times'   => array( '2017-09-14 10:35:43', '2017-09-14 17:24:10' ),
				'expected_days_active' => 1,
			),

			/*
			 * Simulate a new active resource that is activated and deactivated for multiple occasions everyday for 3 days and then left inactive for the rest of the month.
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created
			 * 2. activated for just over an hour, then deactivated for 2'ish hours
			 * 3. activated 4 hours then deactivated for the rest of the period
			 */
			13 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-14 13:13:14', '2017-09-15 09:13:14', '2017-09-15 13:13:14', '2017-09-16 09:13:14', '2017-09-16 13:13:14' ),
				'deactivation_times'   => array( '2017-09-14 10:35:43', '2017-09-14 17:24:10', '2017-09-15 10:35:43', '2017-09-15 17:24:10', '2017-09-16 10:35:43', '2017-09-16 17:24:10' ),
				'expected_days_active' => 3,
			),

			/*
			 * Simulate an existing resource that is activated roughly 12 hours into the first day then left activated
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created
			 * 2. activated for just over an hour, then deactivated for 2'ish hours
			 * 3. activated for the rest of the cycle
			 */
			14 => array(
				'date_created'         => '2017-08-14 09:13:14',
				'activation_times'     => array( '2017-09-14 20:00:03' ),
				'deactivation_times'   => array(),
				'expected_days_active' => 30,
			),

			/*
			 * Simulate an existing resource that is activated for 1 hour into the first day then left activated
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created
			 * 2. activated for just over an hour, then deactivated for 2'ish hours
			 * 3. activated for the rest of the cycle
			 */
			15 => array(
				'date_created'         => '2017-06-14 09:13:14',
				'activation_times'     => array( '2017-09-14 20:00:03', '2017-09-14 22:00:03' ),
				'deactivation_times'   => array( '2017-09-14 21:00:03', '2017-09-15 01:15:11' ),
				'expected_days_active' => 1,
			),

			/*
			 * Simulate an existing resource that is activated for 5ish hours crossing into the next day and then left inactive.
			 * Same overall time as Test 15 (without the multiple activating and deactiving on the same day)
			 *
			 * To test this requires a resource that is:
			 * 0. created prior to the start of the period being checked ($creation_time < $from_timestamp)
			 * 1. activated for 5 hours, then deactivatd for the rest of the cycle
			 */
			16 => array(
				'date_created'         => '2017-06-14 09:13:14',
				'activation_times'     => array( '2017-09-14 20:00:03' ),
				'deactivation_times'   => array( '2017-09-15 01:15:11' ),
				'expected_days_active' => 1,
			),

			/*
			 * Simulate an new resource that is activated for 1 day at the start, then deactivated until the end of the month, then activated and deactivated across a 4hour period.
			 * Similar to Test 15, but this test has multiple activations and deactivations at the end of the test and is also active at the beginning.
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created for 1 day, then deactivated
			 * 2. activated for a just under 3 hours across a 4 hour period, then deactivatd for the rest of the cycle
			 */
			17 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-30 07:00:03', '2017-09-30 09:00:03' ),
				'deactivation_times'   => array( '2017-09-15 09:13:13', '2017-09-30 08:15:11', '2017-09-30 11:00:03' ),
				'expected_days_active' => 3,
			),

			/*
			 * Simulate an new resource that is activated for 1 day, then deactivated until the end of the month for 4 hours.
			 * This test is the same as Test 17 (without the multiple activating and deactiving on the same day)
			 *
			 * To test this requires a resource that is:
			 * 0. created at the same time as the start of the period being checked ($from_timestamp)
			 * 1. activate at the time it is created for 1 day, then deactivated
			 * 2. activated for 4 hours at the end of the cycle, then deactivatd for the rest of the cycle
			 */
			18 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-30 07:00:03' ),
				'deactivation_times'   => array( '2017-09-15 09:13:13', '2017-09-30 11:00:03' ),
				'expected_days_active' => 3,
			),

			19 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-15 09:12:03', '2017-09-30 09:00:03' ),
				'deactivation_times'   => array( '2017-09-15 09:00:13', '2017-09-30 08:15:11', '2017-09-30 11:00:03' ),
				'expected_days_active' => 17,
			),

			20 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-15 09:12:03', '2017-09-30 09:00:03', '2017-10-01 08:00:03' ),
				'deactivation_times'   => array( '2017-09-15 09:00:13', '2017-09-30 08:15:11', '2017-09-30 11:00:03', '2017-10-01 10:00:03' ),
				'expected_days_active' => 18,
			),

			21 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-15 09:12:03', '2017-09-30 09:00:03', '2017-10-02 08:00:03' ),
				'deactivation_times'   => array( '2017-09-15 09:00:13', '2017-09-30 08:15:11', '2017-09-30 11:00:03', '2017-10-02 10:00:03' ),
				'expected_days_active' => 19,
			),


			22 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-15 09:12:03', '2017-09-30 09:00:03', '2017-10-02 09:30:03' ),
				'deactivation_times'   => array( '2017-09-15 09:00:13', '2017-09-30 08:15:11', '2017-09-30 11:00:03', '2017-10-02 10:00:03' ),
				'expected_days_active' => 18,
			),

			23 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-16 09:16:03', '2017-09-30 09:00:03', '2017-10-02 09:30:03' ),
				'deactivation_times'   => array( '2017-09-15 09:00:13', '2017-09-30 08:15:11', '2017-09-30 11:00:03', '2017-10-02 10:00:03' ),
				'expected_days_active' => 17,
			),

			// Demonstrates most basic difference between comparing same day based on calendar days vs 24 hour periods
			// Branch issue_11 picks this up as 2 days
			// This Branch picks this up as 1 day
			24 => array(
				'date_created'         => '2017-09-14 09:13:14',
				'activation_times'     => array( '2017-09-14 09:13:14', '2017-09-15 01:00:00', ),
				'deactivation_times'   => array( '2017-09-14 21:13:13', '2017-09-15 03:00:00', ),
				'expected_days_active' => 1,
			),

			// First activation is on a different day to start AND crosses a "day"
			25 => array(
				'date_created'         => '2017-09-16 08:13:14',
				'activation_times'     => array( '2017-09-16 08:13:14' ),
				'deactivation_times'   => array( '2017-09-16 10:13:14' ),
				'expected_days_active' => 2,
			),

			// First activation is on a different day to start AND crosses a "day", plus another activation that crosses same day
			26 => array(
				'date_created'         => '2017-09-16 08:13:14',
				'activation_times'     => array( '2017-09-16 08:13:14', '2017-09-17 08:13:14' ),
				'deactivation_times'   => array( '2017-09-16 10:13:14', '2017-09-18 08:13:14' ),
				'expected_days_active' => 3,
			),

			// First activation is on a different day to start AND crosses a "day", plus activation that doesn't cross 2 days
			27 => array(
				'date_created'         => '2017-09-16 08:13:14',
				'activation_times'     => array( '2017-09-16 08:13:14', '2017-09-17 10:13:14' ),
				'deactivation_times'   => array( '2017-09-16 10:13:14', '2017-09-18 08:13:14' ),
				'expected_days_active' => 3,
			),

			// First activation is on a different day to start AND crosses a "day", plus more activations which also crosses 2 days
			28 => array(
				'date_created'         => '2017-09-16 08:13:14',
				'activation_times'     => array( '2017-09-16 08:13:14', '2017-09-18 08:13:14' ),
				'deactivation_times'   => array( '2017-09-16 10:13:14', '2017-09-18 10:13:14' ),
				'expected_days_active' => 4,
			),

			// First activation timestamp in array falls out of current renewal period. This is testing the wcsr_get_timestamps_between() function returns `2017-09-15 09:13:14` as the 0th key
			29 => array(
				'date_created'         => '2017-07-14 09:13:14',
				'activation_times'     => array( '2017-07-14 08:13:14', '2017-09-15 09:13:14' ),
				'deactivation_times'   => array( '2017-10-14 09:14:02' ),
				'expected_days_active' => 30,
			),

			// Test for an empty deactivated_timestamps[$i -1] value - this is an impossible case without a bug being in place, i.e you need activation_times[1] to be set with no deactivated_timestamps[0]. This should be impossible because activation_times[1] should never exist without at least deactivation_times[0] between two activation_times
			30 => array(
				'date_created'         => '2017-07-14 09:13:14',
				'activation_times'     => array( 1 => '2017-09-15 09:13:14' ),
				'deactivation_times'   => array(),
				'expected_days_active' => 30,
			),

			// A test case testing a 6month old subscription with a store that has multiple activations/deactivations at the beginning then left active
			31 => array(
				'date_created'         => '2017-03-14 09:13:14',
				'activation_times'     => array(
					'2017-03-14 09:13:14', // 14th MAR
					'2017-03-30 20:13:14', // 30th MAR
					'2017-05-05 20:13:14', // 5th  MAY
				),
				'deactivation_times'   => array(
					'2017-03-20 08:13:50', // 20th MAR
					'2017-04-01 20:13:14', // 1st  APR
				),
				'expected_days_active' => 31,
			),

			// A test case testing a 6month old subscription with a store that has multiple activations/deactivations thoughout, including the current period
			32 => array(
				'date_created'         => '2017-03-14 09:13:14',
				'activation_times'     => array(
					'2017-03-14 09:13:14', // 14th MAR
					'2017-03-30 20:13:14', // 30th MAR
					'2017-05-05 20:13:14', // 5th  MAY
					'2017-07-01 14:19:40', // 1st  JUL
					'2017-07-05 14:19:40', // 5th  JUL
					'2017-08-17 14:19:40', // 17th AUG
					'2017-10-10 14:19:41', // 10th OCT (activated for 3 days)
				),
				'deactivation_times'   => array(
					'2017-03-20 08:13:50', // 20th MAR
					'2017-04-01 20:13:14', // 1st  APR
					'2017-06-30 20:13:14', // 30th JUN
					'2017-07-02 14:19:40', // 2nd  JUL
					'2017-08-15 14:19:40', // 15th AUG
					'2017-09-19 14:19:40', // 19th SEPT (deactivated for the first time 5/6 days into current period)
					'2017-10-13 14:19:40', // 13th OCT
				),
				'expected_days_active' => 9,
			),

			// Tests for having 0 deactivation times between the from and to timestamps, and the second activation timestamp (at index 1) being the only timestamp within the from and to timestamps.
			33 => array(
				'date_created'         => '2017-08-14 09:13:14',
				'activation_times'     => array( '2017-08-14 09:13:14', '2017-09-20 09:13:14' ),
				'deactivation_times'   => array( '2017-09-01 09:13:14' ),
				'expected_days_active' => 25,
			),

			// Tests for having 0 days active
			34 => array(
				'date_created'         => '2017-08-14 09:13:14',
				'activation_times'     => array( '2017-08-14 09:13:14' ),
				'deactivation_times'   => array( '2017-08-15 09:13:14' ),
				'expected_days_active' => 0,
			),
		);
	}

	/**
	 * Make sure get_days_active() handles all calculation scenarios
	 *
	 * @dataProvider provider_get_days_active
	 */
	public function test_get_days_active( $date_created_string, $activation_times, $deactivation_times, $expected_days_active ) {

		$date_created = new DateTime();
		$date_created->setTimestamp( strtotime( $date_created_string ) );

		// Convert activation/deactivate dates to timestamps
		$activation_times   = array_map( 'strtotime', $activation_times );
		$deactivation_times = array_map( 'strtotime', $deactivation_times );

		$resource_mock = $this->getMockBuilder( 'WCSR_Resource' )->setMethods( array( 'get_date_created', 'has_been_activated', 'get_activation_timestamps', 'get_deactivation_timestamps' ) )->disableOriginalConstructor()->getMock();
		$resource_mock->expects( $this->any() )->method( 'get_date_created' )->will( $this->returnValue( $date_created ) );
		$resource_mock->expects( $this->any() )->method( 'has_been_activated' )->will( $this->returnValue( true ) );
		$resource_mock->expects( $this->any() )->method( 'get_activation_timestamps' )->will( $this->returnValue( $activation_times ) );
		$resource_mock->expects( $this->any() )->method( 'get_deactivation_timestamps' )->will( $this->returnValue( $deactivation_times ) );

		$this->assertEquals( $expected_days_active, $resource_mock->get_days_active( self::$from_timestamp, self::$to_timestamp ) );
	}

	/**
	 * Provide data to test is active
	 */
	public function provider_is_active() {
		return array(
			// test the first renewal case.. i.e check that the $at_timestamp is the same as the first activation timestamp
			0 => array(
				'activation_times'   => array( '2017-09-14 09:13:14' ),
				'deactivation_times' => array( '2017-10-14 11:01:40' ),
				'at_timestamp'       => strtotime( '2017-09-14 09:13:14' ),
				'expected_is_active' => true,
			),

			// active at time, but deactivate one day after $at_timestamp
			1 => array(
				'activation_times'   => array( '2018-07-20 09:13:14' ),
				'deactivation_times' => array( '2018-08-19 11:01:40' ),
				'at_timestamp'       => strtotime( '2018-08-18 09:13:14' ),
				'expected_is_active' => true,
			),

			// Active for a day then deactivated for three months, then activated again. This test is checking in the middle of the two active periods whether
			2 => array(
				'activation_times'   => array( '2018-07-10 09:13:14', '2018-10-20 10:20:20' ),
				'deactivation_times' => array( '2018-07-11 11:01:40', '2018-10-30 10:30:12' ),
				'at_timestamp'       => strtotime( '2018-09-18 09:13:14' ),
				'expected_is_active' => false,
			),

			// Active for a day then deactivated for three months, then activated again. This test is checking in the middle of the two active periods whether
			3 => array(
				'activation_times'   => array( '2018-07-20 09:13:14', '2018-10-20 10:20:20' ),
				'deactivation_times' => array( '2018-07-21 11:01:40' ),
				'at_timestamp'       => strtotime( '2018-08-18 09:13:14' ), // not active now, but will be active in two days
				'expected_is_active' => false,
			),

			// Check if a resource is active at a specific time, when the resource has never been activated.
			4 => array(
				'activation_times'   => array(),
				'deactivation_times' => array(),
				'at_timestamp'       => strtotime( '2018-08-18 09:13:14' ), // not active now, but will be active in two days
				'expected_is_active' => false,
			),

			// Check if a resource is active at the current time, when the resource has never been activated.
			5 => array(
				'activation_times'   => array(),
				'deactivation_times' => array(),
				'at_timestamp'       => null,
				'expected_is_active' => false,
			),

			// Check if a resource is active at the current time, when the resource has never been deactivated.
			6 => array(
				'activation_times'   => array( '2017-07-20 09:13:14' ),
				'deactivation_times' => array(),
				'at_timestamp'       => null,
				'expected_is_active' => true,
			),

			// Check if a resource is active at the current time, when the resource was activated for 1 minute in the past
			7 => array(
				'activation_times'   => array( '2017-07-20 09:13:14' ),
				'deactivation_times' => array( '2017-07-20 09:14:50' ),
				'at_timestamp'       => null,
				'expected_is_active' => false,
			),

			// Check if a resource is active at time that is before it was even first activated
			8 => array(
				'activation_times'   => array( '2017-07-20 09:13:14' ),
				'deactivation_times' => array( '2017-07-20 09:14:50' ),
				'at_timestamp'       => strtotime( '2017-06-20 09:14:50' ),
				'expected_is_active' => false,
			),

			// Check if a resource is active at some time in the future when the resource was only activated for 1 minute in the past
			9 => array(
				'activation_times'   => array( '2017-07-20 09:13:14' ),
				'deactivation_times' => array( '2017-07-20 09:14:50' ),
				'at_timestamp'       => strtotime( '2018-08-10 10:10:10' ),
				'expected_is_active' => false,
			),

			// Test if a resource was active at the exact same time that it was deactivated
			10 => array(
				'activation_times'   => array( '2017-07-20 09:13:14' ),
				'deactivation_times' => array( '2017-07-20 09:14:50' ),
				'at_timestamp'       => strtotime( '2017-07-20 09:14:50' ),
				'expected_is_active' => false,
			),

			// Test if a resource was active 1 second before it was deactivated
			11 => array(
				'activation_times'   => array( '2017-07-20 09:13:14' ),
				'deactivation_times' => array( '2017-07-20 09:14:50' ),
				'at_timestamp'       => strtotime( '2017-07-20 09:14:49' ),
				'expected_is_active' => true,
			),

			// Check if a resource was active at time that is before it was even first activated
			12 => array(
				'activation_times'   => array( '2017-07-20 09:13:14' ),
				'deactivation_times' => array(),
				'at_timestamp'       => strtotime( '2017-06-20 09:14:50' ),
				'expected_is_active' => false,
			),
		);
	}

	/**
	 * Test case for $resource->is_active()
	 *
	 * @dataProvider provider_is_active
	 */
	public function test_is_active( $activation_times, $deactivation_times, $at_timestamp, $expected_is_active ) {
		// Convert activation/deactivate dates to timestamps
		$activation_times   = array_map( 'strtotime', $activation_times );
		$deactivation_times = array_map( 'strtotime', $deactivation_times );

		$resource_mock = $this->getMockBuilder( 'WCSR_Resource' )->setMethods( array( 'has_been_activated', 'get_activation_timestamps', 'get_deactivation_timestamps' ) )->disableOriginalConstructor()->getMock();
		$resource_mock->expects( $this->any() )->method( 'has_been_activated' )->will( $this->returnValue( true ) );
		$resource_mock->expects( $this->any() )->method( 'get_activation_timestamps' )->will( $this->returnValue( $activation_times ) );
		$resource_mock->expects( $this->any() )->method( 'get_deactivation_timestamps' )->will( $this->returnValue( $deactivation_times ) );

		$this->assertEquals( $expected_is_active, $resource_mock->is_active( $at_timestamp ) );
	}
}
