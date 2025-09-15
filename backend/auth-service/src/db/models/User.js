import { DataTypes } from 'sequelize';
import sequelize from '../index.js';
import Role from './Role.js';

const User = sequelize.define('User', {
  userId: { 
    type: DataTypes.STRING,
    primaryKey: true 
  },
  name: { 
    type: DataTypes.STRING, 
    allowNull: false 
  },
  email: { 
    type: DataTypes.STRING, 
    allowNull: false,
    unique: true, 
  },
  passwordHash: { 
    type: DataTypes.STRING, 
    allowNull: false 
  },
  phone: { 
    type: DataTypes.STRING 
  },
  dob: { 
    type: DataTypes.DATEONLY 
  },
  gender: { 
    type: DataTypes.ENUM('male', 'female') 
  },
  yearOfEducation: { 
    type: DataTypes.STRING 
  },
  branchId: { 
    type: DataTypes.STRING 
  },
  collegeId: { 
    type: DataTypes.STRING
  },
  yearOfStudy: { 
    type: DataTypes.INTEGER 
  },
  orgId: { 
    type: DataTypes.STRING 
  },
  assessmentId: { 
    type: DataTypes.STRING
  },
  programInterested: { 
    type: DataTypes.STRING 
  },
  profileStatus: { 
    type: DataTypes.ENUM('active', 'inactive', 'pending'), 
    defaultValue: 'pending' 
  },
  location: { 
    type: DataTypes.STRING 
  },
  lastLogin: { 
    type: DataTypes.DATE 
  },
  refreshToken: {  
    type: DataTypes.STRING(1024),
    allowNull: true
  },
  roleId: {  // foreign key reference
    type: DataTypes.STRING,
    references: {
      model: Role,
      key: 'roleId'
    },
    allowNull: false
  }
}, {
  tableName: 'users',
  timestamps: true, // adds createdAt and updatedAt automatically
});

// Associations
User.belongsTo(Role, { foreignKey: 'roleId', as: 'role' });
Role.hasMany(User, { foreignKey: 'roleId', as: 'users' });


export default User;
